<?php

namespace App\Services\Stock;

use App\Enums\StockMovementType;
use App\Events\StockLevelLow;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Site;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Support\BatchAttributes;
use App\Support\Fmt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Niveaux "avant mouvement" des couples site/produit touchés par une sortie,
     * pour détecter le franchissement du seuil de rupture.
     *
     * @var array<string, array{site_id:int, product_id:int, before:float, threshold:float}>
     */
    private array $watched = [];

    /**
     * Enregistre un mouvement et met à jour les stocks dans une transaction.
     *
     * @param  array{type:string|StockMovementType, product_id?:int, batch_id?:int|null, source_site_id?:int,
     *               destination_site_id?:int, site_id?:int, quantity?:float, counted_quantity?:float,
     *               reason?:string|null, notes?:string|null, moved_at?:string|null}  $data
     */
    public function record(array $data, ?User $user = null): StockMovement
    {
        $type = $data['type'] instanceof StockMovementType ? $data['type'] : StockMovementType::from($data['type']);
        [$productId, $batch] = $this->resolveProduct($data);
        $batchId = $batch?->getKey();
        $movedAt = ! empty($data['moved_at']) ? Carbon::parse($data['moved_at']) : now();
        $quantity = round((float) ($data['quantity'] ?? 0), 2);

        if ($type !== StockMovementType::Adjustment && $quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => 'La quantité doit être supérieure à zéro.']);
        }

        $this->watched = [];

        $movement = DB::transaction(function () use ($type, $data, $productId, $batch, $batchId, $movedAt, $quantity, $user) {
            $source = null;
            $destination = null;

            switch ($type) {
                case StockMovementType::In:
                    $destination = (int) $data['destination_site_id'];
                    $this->guardBatchUsable($batch, 'l’entrée en stock');
                    $this->increase($destination, $productId, $batchId, $quantity, $movedAt);
                    break;

                case StockMovementType::Out:
                    $source = (int) $data['source_site_id'];
                    $this->decrease($source, $productId, $batchId, $quantity, $movedAt);
                    break;

                case StockMovementType::Transfer:
                    $source = (int) $data['source_site_id'];
                    $destination = (int) $data['destination_site_id'];

                    if ($source === $destination) {
                        throw ValidationException::withMessages([
                            'destination_site_id' => 'Le site de destination doit être différent du site d’origine.',
                        ]);
                    }

                    $this->guardBatchUsable($batch, 'le transfert');
                    $this->decrease($source, $productId, $batchId, $quantity, $movedAt);
                    $this->increase($destination, $productId, $batchId, $quantity, $movedAt);
                    break;

                case StockMovementType::Adjustment:
                    $siteId = (int) $data['site_id'];
                    $current = (float) ($this->findLine($siteId, $productId, $batchId, lock: true)?->quantity ?? 0);
                    $delta = round((float) $data['counted_quantity'] - $current, 2);

                    if (abs($delta) < 0.01) {
                        throw ValidationException::withMessages([
                            'counted_quantity' => 'Aucun écart avec le stock enregistré ('.Fmt::q($current).') : rien à ajuster.',
                        ]);
                    }

                    $quantity = abs($delta);

                    if ($delta > 0) {
                        $destination = $siteId;
                        $this->increase($siteId, $productId, $batchId, $quantity, $movedAt, checkCapacity: false);
                    } else {
                        $source = $siteId;
                        $this->decrease($siteId, $productId, $batchId, $quantity, $movedAt);
                    }
                    break;
            }

            return StockMovement::create([
                'type' => $type,
                'product_id' => $productId,
                'batch_id' => $batchId,
                'source_site_id' => $source,
                'destination_site_id' => $destination,
                'quantity' => $quantity,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' => $user?->getKey(),
                'moved_at' => $movedAt,
            ]);
        });

        $this->dispatchThresholdCrossings();

        return $movement;
    }

    /** Crée une ligne de stock ; la quantité initiale passe par un mouvement d'entrée (traçabilité). */
    public function openLine(array $data, ?User $user = null): Stock
    {
        [$productId, $batch] = $this->resolveProduct($data);
        $batchId = $batch?->getKey();
        $siteId = (int) $data['site_id'];

        return DB::transaction(function () use ($data, $user, $productId, $batchId, $siteId) {
            if ($this->findLine($siteId, $productId, $batchId)) {
                throw ValidationException::withMessages([
                    'site_id' => 'Cette ligne de stock existe déjà sur ce site : enregistrez plutôt un mouvement d’entrée.',
                ]);
            }

            $quantity = (float) ($data['quantity'] ?? 0);

            if ($quantity > 0) {
                $this->record([
                    'type' => StockMovementType::In,
                    'destination_site_id' => $siteId,
                    'product_id' => $productId,
                    'batch_id' => $batchId,
                    'quantity' => $quantity,
                    'reason' => 'Stock initial',
                ], $user);
            } else {
                Stock::create(['site_id' => $siteId, 'product_id' => $productId, 'batch_id' => $batchId]);
            }

            if (array_key_exists('min_threshold', $data) && $data['min_threshold'] !== null) {
                $this->setThreshold($siteId, $productId, (float) $data['min_threshold']);
            }

            return $this->findLine($siteId, $productId, $batchId);
        });
    }

    /** Le seuil s'applique au produit sur le site : toutes ses lignes (lots) sont mises à jour. */
    public function setThreshold(int $siteId, int $productId, float $threshold): void
    {
        Stock::query()
            ->where('site_id', $siteId)
            ->where('product_id', $productId)
            ->update(['min_threshold' => max(0, round($threshold, 2))]);
    }

    public function thresholdFor(int $siteId, int $productId): float
    {
        return (float) Stock::query()
            ->where('site_id', $siteId)
            ->where('product_id', $productId)
            ->max('min_threshold');
    }

    /** Stock disponible d'un produit sur un site (hors lots rappelés ou périmés). */
    public function availableAt(int $siteId, int $productId): float
    {
        return (float) Stock::query()
            ->with('batch')
            ->where('site_id', $siteId)
            ->where('product_id', $productId)
            ->get()
            ->sum(fn (Stock $line) => $line->availableQuantity());
    }

    /** Consommation moyenne journalière (sorties) d'un produit sur un site. */
    public function averageDailyConsumption(int $siteId, int $productId, int $days = 30): float
    {
        $days = max(1, $days);

        $total = (float) StockMovement::query()
            ->sales()
            ->where('source_site_id', $siteId)
            ->where('product_id', $productId)
            ->where('moved_at', '>=', now()->subDays($days))
            ->sum('quantity');

        return $total / $days;
    }

    /**
     * Couples site/produit dont le stock disponible est sous le seuil.
     *
     * @return Collection<int, object{key:string, site:Site, product:?Product, available:float, threshold:float}>
     */
    public function lowStock(): Collection
    {
        return Stock::query()
            ->with(['site', 'product', 'batch'])
            ->where('min_threshold', '>', 0)
            ->get()
            ->groupBy(fn (Stock $line) => $line->site_id.'-'.$line->product_id)
            ->map(fn (Collection $lines, string $key) => (object) [
                'key' => $key,
                'site' => $lines->first()->site,
                'product' => $lines->first()->product,
                'available' => (float) $lines->sum(fn (Stock $l) => $l->availableQuantity()),
                'threshold' => (float) $lines->max('min_threshold'),
            ])
            ->filter(fn ($row) => $row->available <= $row->threshold)
            ->sortBy('available')
            ->values();
    }

    /**
     * Recalcule chaque ligne à partir de l'historique des mouvements.
     *
     * @return Collection<int, array{stock:Stock, recorded:float, computed:float}>
     */
    public function recalculate(bool $fix = false): Collection
    {
        $report = collect();

        Stock::query()->with(['site', 'product', 'batch'])->each(function (Stock $line) use ($fix, $report) {
            $base = fn () => StockMovement::query()
                ->where('product_id', $line->product_id)
                ->forBatch($line->batch_id);

            $computed = round(
                (float) $base()->where('destination_site_id', $line->site_id)->sum('quantity')
                - (float) $base()->where('source_site_id', $line->site_id)->sum('quantity'),
                2
            );

            if (abs($computed - $line->quantity) >= 0.01) {
                $report->push(['stock' => $line, 'recorded' => $line->quantity, 'computed' => $computed]);

                if ($fix) {
                    $line->update(['quantity' => $computed]);
                }
            }
        });

        return $report;
    }

    public function findLine(int $siteId, int $productId, ?int $batchId, bool $lock = false): ?Stock
    {
        return Stock::query()
            ->where('site_id', $siteId)
            ->where('product_id', $productId)
            ->forBatch($batchId)
            ->when($lock, fn ($q) => $q->lockForUpdate())
            ->first();
    }

    private function increase(int $siteId, int $productId, ?int $batchId, float $quantity, Carbon $at, bool $checkCapacity = true): void
    {
        $site = Site::query()->lockForUpdate()->findOrFail($siteId);

        if ($checkCapacity && $site->capacity > 0) {
            $used = (float) Stock::query()->where('site_id', $siteId)->sum('quantity');

            if ($used + $quantity > $site->capacity + 0.001) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Capacité de %s dépassée : %s place(s) libre(s) pour %s demandée(s).',
                        $site->name, Fmt::q(max(0, $site->capacity - $used)), Fmt::q($quantity)
                    ),
                ]);
            }
        }

        $line = $this->findLine($siteId, $productId, $batchId, lock: true)
            ?? new Stock([
                'site_id' => $siteId,
                'product_id' => $productId,
                'batch_id' => $batchId,
                'quantity' => 0,
                'min_threshold' => $this->thresholdFor($siteId, $productId),
            ]);

        $line->quantity = round($line->quantity + $quantity, 2);
        $line->last_movement_at = $at;
        $line->save();
    }

    private function decrease(int $siteId, int $productId, ?int $batchId, float $quantity, Carbon $at): void
    {
        $this->watch($siteId, $productId);

        $line = $this->findLine($siteId, $productId, $batchId, lock: true);
        $current = (float) ($line?->quantity ?? 0);

        if ($current + 0.001 < $quantity) {
            $site = Site::query()->find($siteId);

            throw ValidationException::withMessages([
                'quantity' => sprintf(
                    'Stock insuffisant sur %s : %s disponible(s), %s demandé(s).',
                    $site?->name ?? 'ce site', Fmt::q($current), Fmt::q($quantity)
                ),
            ]);
        }

        $line->quantity = round($current - $quantity, 2);
        $line->last_movement_at = $at;
        $line->save();
    }

    private function watch(int $siteId, int $productId): void
    {
        $key = $siteId.'-'.$productId;

        $this->watched[$key] ??= [
            'site_id' => $siteId,
            'product_id' => $productId,
            'before' => $this->availableAt($siteId, $productId),
            'threshold' => $this->thresholdFor($siteId, $productId),
        ];
    }

    /** Alerte uniquement au franchissement du seuil (pas à chaque sortie sous le seuil). */
    private function dispatchThresholdCrossings(): void
    {
        foreach ($this->watched as $watch) {
            if ($watch['threshold'] <= 0) {
                continue;
            }

            $after = $this->availableAt($watch['site_id'], $watch['product_id']);

            if ($watch['before'] > $watch['threshold'] && $after <= $watch['threshold']) {
                StockLevelLow::dispatch(
                    Site::query()->findOrFail($watch['site_id']),
                    Product::query()->findOrFail($watch['product_id']),
                    $after,
                    $watch['threshold'],
                );
            }
        }

        $this->watched = [];
    }

    /** @return array{0:int, 1:?Batch} */
    private function resolveProduct(array $data): array
    {
        $batch = ! empty($data['batch_id']) ? Batch::query()->findOrFail($data['batch_id']) : null;
        $productId = $batch?->product_id ?: ($data['product_id'] ?? null);

        if (! $productId) {
            throw ValidationException::withMessages(['product_id' => 'Sélectionnez un produit ou un lot.']);
        }

        if ($batch && ! empty($data['product_id']) && (int) $data['product_id'] !== (int) $batch->product_id) {
            throw ValidationException::withMessages(['batch_id' => 'Ce lot n’appartient pas au produit sélectionné.']);
        }

        return [(int) $productId, $batch];
    }

    private function guardBatchUsable(?Batch $batch, string $operation): void
    {
        if (! $batch) {
            return;
        }

        if (BatchAttributes::isBlocked($batch)) {
            throw ValidationException::withMessages([
                'batch_id' => 'Le lot '.BatchAttributes::code($batch).' est rappelé : '.$operation.' est interdit.',
            ]);
        }

        if (BatchAttributes::isExpired($batch)) {
            throw ValidationException::withMessages([
                'batch_id' => 'Le lot '.BatchAttributes::code($batch).' a dépassé sa DLC : '.$operation.' est interdit.',
            ]);
        }
    }
}
