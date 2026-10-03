<?php use \App\Support\Fmt; ?>
<?php use \App\Support\BatchAttributes; ?>
<?php use \App\Models\Stock; ?>

<?php $__env->startSection('eyebrow', 'Inventory workspace'); ?>
<?php $__env->startSection('title', 'Stocks par site'); ?>
<?php $__env->startSection('description', 'Quantités par produit et par lot sur chaque site actif. Les lots rappelés ou périmés restent comptés en stock physique mais ne sont pas disponibles.'); ?>
<?php $__env->startSection('page-action'); ?>
    <a class="btn btn-ghost" href="<?php echo e(route('stock-movements.create')); ?>">Enregistrer un mouvement</a>
    <a class="btn btn-primary" href="<?php echo e(route('stocks.create')); ?>">Ajouter une ligne de stock</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.impact', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="figures">
    <div class="figure"><strong><?php echo e($kpis['sites']); ?></strong><span>sites affichés</span></div>
    <div class="figure"><strong><?php echo e(Fmt::q($kpis['quantity'])); ?></strong><span>unités en stock</span></div>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['figure', 'is-alert' => $kpis['low'] > 0]); ?>"><strong><?php echo e($kpis['low']); ?></strong><span>produits sous leur seuil</span></div>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['figure', 'is-warn' => $kpis['expiring'] > 0]); ?>"><strong><?php echo e($kpis['expiring']); ?></strong><span>lignes à DLC proche ou dépassée</span></div>
</div>

<?php if($low->isNotEmpty()): ?>
    <section class="notice notice-error" role="alert">
        <p class="notice-title">Risque de rupture</p>
        <ul>
            <?php $__currentLoopData = $low; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <b><?php echo e($row->product?->name); ?></b> à <?php echo e($row->site?->name); ?> :
                    <?php echo e(Fmt::q($row->available)); ?> disponible(s) pour un seuil de <?php echo e(Fmt::q($row->threshold)); ?>.
                    <a href="<?php echo e(route('stock-movements.create', ['type' => 'transfer'])); ?>">Réapprovisionner</a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </section>
<?php endif; ?>

<?php if($predicted->isNotEmpty()): ?>
    <section class="notice notice-warning">
        <p class="notice-title">Ruptures prévues dans les 7 prochains jours</p>
        <ul>
            <?php $__currentLoopData = $predicted; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <b><?php echo e($row->product?->name); ?></b> à <?php echo e($row->site?->name); ?> :
                    <?php echo e(Fmt::q($row->available)); ?> u. en stock pour environ <?php echo e(Fmt::n($row->daily, 1)); ?> u. vendues par jour,
                    rupture prévue <b><?php echo e($row->days === 0 ? 'aujourd’hui' : $row->date->locale('fr')->isoFormat('dddd DD/MM')); ?></b>.
                    <a href="<?php echo e(route('forecast.index', ['site_id' => $row->site?->id, 'product_id' => $row->product?->id])); ?>">Voir la prévision</a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </section>
<?php endif; ?>

<form class="filters" method="GET" action="<?php echo e(route('stocks.index')); ?>">
    <div class="field">
        <label for="f-site">Site</label>
        <select id="f-site" name="site_id">
            <option value="">Tous les sites</option>
            <?php $__currentLoopData = $allSites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s->id); ?>" <?php if(($filters['site_id'] ?? null) == $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="field">
        <label for="f-product">Produit</label>
        <select id="f-product" name="product_id">
            <option value="">Tous les produits</option>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($p->id); ?>" <?php if(($filters['product_id'] ?? null) == $p->id): echo 'selected'; endif; ?>><?php echo e($p->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="field">
        <label for="f-state">État</label>
        <select id="f-state" name="state">
            <option value="">Tous les états</option>
            <?php $__currentLoopData = ['low', 'expiring', 'expired', 'recalled', 'ok']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($state); ?>" <?php if(($filters['state'] ?? null) === $state): echo 'selected'; endif; ?>><?php echo e(Stock::stateLabel($state)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <button class="btn btn-ghost" type="submit">Filtrer</button>
    <?php if($isFiltered): ?>
        <a class="btn btn-ghost" href="<?php echo e(route('stocks.index')); ?>">Effacer les filtres</a>
    <?php endif; ?>
</form>

<?php $__empty_1 = true; $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php $lines = $linesBySite->get($site->id, collect()); ?>
    <?php if($isFiltered && $lines->isEmpty() && empty($filters['site_id'])) continue; ?>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2><?php echo e($site->name); ?></h2>
                <p class="muted small"><?php echo e($site->type->label()); ?><?php if($site->city): ?>, <?php echo e($site->city); ?><?php endif; ?>. Code <?php echo e($site->code); ?></p>
            </div>
            <?php echo $__env->make('stocks._rack', ['used' => $site->usedCapacity(), 'capacity' => $site->capacity], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <?php if($lines->isEmpty()): ?>
            <p class="empty">Aucun stock ne correspond sur ce site. <a href="<?php echo e(route('stocks.create', ['site_id' => $site->id])); ?>">Ajouter une ligne</a></p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Lot</th>
                        <th>DLC</th>
                        <th class="num">Quantité</th>
                        <th class="num">Disponible</th>
                        <th class="num">Seuil</th>
                        <th>État</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $expiry = BatchAttributes::expiryDate($line->batch); ?>
                        <tr>
                            <td><?php echo e($line->product?->name ?? 'Produit supprimé'); ?></td>
                            <td class="code"><?php echo e($line->batch ? BatchAttributes::code($line->batch) : 'Sans lot'); ?></td>
                            <td><?php echo e($expiry?->format('d/m/Y') ?? '—'); ?></td>
                            <td class="num"><?php echo e(Fmt::q($line->quantity)); ?></td>
                            <td class="num"><?php echo e(Fmt::q($line->availableQuantity())); ?></td>
                            <td class="num"><?php echo e(Fmt::q($line->min_threshold)); ?></td>
                            <td><span class="tag tag-<?php echo e($line->display_state); ?>"><?php echo e(Stock::stateLabel($line->display_state)); ?></span></td>
                            <td class="row-actions">
                                <a href="<?php echo e(route('stocks.show', $line)); ?>">Historique</a>
                                <a href="<?php echo e(route('stocks.edit', $line)); ?>">Seuil</a>
                                <?php if($line->batch && $line->availableQuantity() > 0): ?>
                                    <a href="<?php echo e(route('optimization.create', ['batch_id' => $line->batch_id])); ?>">Optimiser</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="panel empty">
        <p>Aucun site actif. Déclarez vos entrepôts et magasins pour commencer à suivre les stocks.</p>
        <a class="btn btn-primary" href="<?php echo e(route('sites.create')); ?>">Ajouter un site</a>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\stocks\index.blade.php ENDPATH**/ ?>