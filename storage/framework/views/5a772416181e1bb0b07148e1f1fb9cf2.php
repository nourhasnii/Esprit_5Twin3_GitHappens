<?php use \App\Support\Fmt; ?>
<?php use \App\Support\BatchAttributes; ?>
<?php use \App\Enums\StockMovementType; ?>

<?php $__env->startSection('eyebrow', 'Inventory workspace'); ?>
<?php $__env->startSection('title', 'Mouvements de stock'); ?>
<?php $__env->startSection('description', 'Chaque entrée, sortie, transfert ou ajustement, dans l’ordre où il a eu lieu. Les stocks sont recalculables à partir de cet historique.'); ?>
<?php $__env->startSection('page-action'); ?>
    <a class="btn btn-primary" href="<?php echo e(route('stock-movements.create')); ?>">Enregistrer un mouvement</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="figures">
    <?php $__currentLoopData = StockMovementType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $t = $totals->get($type->value); ?>
        <div class="figure">
            <strong><?php echo e(Fmt::q($t->quantity ?? 0)); ?></strong>
            <span><?php echo e(mb_strtolower($type->label())); ?>s, <?php echo e($t->movements ?? 0); ?> mouvement(s)</span>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<form class="filters" method="GET" action="<?php echo e(route('stock-movements.index')); ?>">
    <div class="field">
        <label for="f-type">Type</label>
        <select id="f-type" name="type">
            <option value="">Tous</option>
            <?php $__currentLoopData = StockMovementType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($type->value); ?>" <?php if(($filters['type'] ?? null) === $type->value): echo 'selected'; endif; ?>><?php echo e($type->label()); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="field">
        <label for="f-site">Site</label>
        <select id="f-site" name="site_id">
            <option value="">Tous les sites</option>
            <?php $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
        <label for="f-from">Du</label>
        <input type="date" id="f-from" name="from" value="<?php echo e($filters['from'] ?? ''); ?>">
    </div>
    <div class="field">
        <label for="f-to">Au</label>
        <input type="date" id="f-to" name="to" value="<?php echo e($filters['to'] ?? ''); ?>">
    </div>
    <button class="btn btn-ghost" type="submit">Filtrer</button>
    <?php if(array_filter($filters)): ?>
        <a class="btn btn-ghost" href="<?php echo e(route('stock-movements.index')); ?>">Effacer</a>
    <?php endif; ?>
</form>

<section class="panel">
    <?php if($movements->isEmpty()): ?>
        <div class="empty">
            <p>Aucun mouvement ne correspond à ces critères.</p>
            <a class="btn btn-primary" href="<?php echo e(route('stock-movements.create')); ?>">Enregistrer un mouvement</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Référence</th>
                    <th>Type</th>
                    <th>Produit et lot</th>
                    <th>Origine</th>
                    <th>Destination</th>
                    <th class="num">Quantité</th>
                    <th>Motif</th>
                    <th>Par</th>
                </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="white-space:nowrap"><?php echo e($m->moved_at->format('d/m/Y H:i')); ?></td>
                        <td class="code"><?php echo e($m->reference); ?></td>
                        <td><span class="tag tag-<?php echo e($m->type->value); ?>"><?php echo e($m->type->label()); ?></span></td>
                        <td>
                            <?php echo e($m->product?->name); ?>

                            <div class="muted small"><?php echo e($m->batch ? 'Lot '.BatchAttributes::code($m->batch) : 'Sans lot'); ?></div>
                        </td>
                        <td><?php echo e($m->sourceSite?->name ?? '—'); ?></td>
                        <td><?php echo e($m->destinationSite?->name ?? '—'); ?></td>
                        <td class="num"><?php echo e(Fmt::q($m->quantity)); ?></td>
                        <td><?php echo e($m->reason ?? '—'); ?></td>
                        <td class="small"><?php echo e($m->user?->name ?? 'Système'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<div class="pager"><?php echo e($movements->links('pagination::default')); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views/stock-movements/index.blade.php ENDPATH**/ ?>