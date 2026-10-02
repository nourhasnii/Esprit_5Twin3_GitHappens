<?php

namespace App\Console\Commands;

use App\Services\Stock\StockService;
use App\Support\BatchAttributes;
use Illuminate\Console\Command;

class RecalculateStocks extends Command
{
    protected $signature = 'stocks:recalculate {--fix : Corrige les lignes en écart}';

    protected $description = 'Recalcule les stocks à partir de l’historique des mouvements et signale les écarts';

    public function handle(StockService $service): int
    {
        $report = $service->recalculate((bool) $this->option('fix'));

        if ($report->isEmpty()) {
            $this->info('Aucun écart : les stocks correspondent à l’historique des mouvements.');

            return self::SUCCESS;
        }

        $this->table(
            ['Site', 'Produit', 'Lot', 'Enregistré', 'Recalculé'],
            $report->map(fn (array $row) => [
                $row['stock']->site?->name,
                $row['stock']->product?->name,
                $row['stock']->batch ? BatchAttributes::code($row['stock']->batch) : 'Sans lot',
                $row['recorded'],
                $row['computed'],
            ])->all()
        );

        $this->option('fix')
            ? $this->info($report->count().' ligne(s) corrigée(s).')
            : $this->warn('Relancez avec --fix pour corriger ces lignes.');

        return self::SUCCESS;
    }
}
