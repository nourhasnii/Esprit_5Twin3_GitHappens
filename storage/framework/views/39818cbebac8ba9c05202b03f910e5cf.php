<?php use \App\Support\Fmt; ?>
<?php use \App\Support\BatchAttributes; ?>

<?php $__env->startSection('title', 'Historique de stock'); ?>
<?php $__env->startSection('eyebrow', 'Inventory workspace'); ?>
<?php $__env->startSection('description', $stock->product?->name.' à '.$stock->site?->name.' · '.($stock->batch ? 'Lot '.BatchAttributes::code($stock->batch) : 'Stock suivi sans lot').(BatchAttributes::expiryDate($stock->batch) ? ' · DLC le '.BatchAttributes::expiryDate($stock->batch)->format('d/m/Y') : '')); ?>
<?php $__env->startSection('page-action'); ?>
    <a class="btn btn-ghost" href="<?php echo e(route('stocks.index', ['site_id' => $stock->site_id])); ?>">Retour aux stocks</a>
    <a class="btn btn-ghost" href="<?php echo e(route('stock-movements.create', ['type' => 'adjustment'])); ?>">Ajuster le stock</a>
    <?php if($stock->batch && $stock->availableQuantity() > 0): ?>
        <a class="btn btn-primary" href="<?php echo e(route('optimization.create', ['batch_id' => $stock->batch_id])); ?>">Optimiser</a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php $expiry = BatchAttributes::expiryDate($stock->batch); $days = BatchAttributes::daysToExpiry($stock->batch); ?>

<div class="figures">
    <div class="figure"><strong><?php echo e(Fmt::q($stock->quantity)); ?></strong><span>en stock sur cette ligne</span></div>
    <div class="figure"><strong><?php echo e(Fmt::q($productAvailable)); ?></strong><span>disponibles pour ce produit sur le site</span></div>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['figure', 'is-alert' => $stock->min_threshold > 0 && $productAvailable <= $stock->min_threshold]); ?>"><strong><?php echo e(Fmt::q($stock->min_threshold)); ?></strong><span>seuil de rupture</span></div>
    <div class="figure"><strong><?php echo e(Fmt::n($avgDaily, 1)); ?></strong><span>sorties par jour en moyenne sur <?php echo e($window); ?> jours</span></div>
</div>

<section class="panel">
    <div class="panel-head"><h2>Mouvements de cette ligne</h2></div>
    <?php if($movements->isEmpty()): ?>
        <p class="empty">Aucun mouvement enregistré.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Date</th><th>Référence</th><th>Type</th><th>Provenance ou destination</th><th class="num">Variation</th><th>Motif</th><th>Par</th></tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $signed = $m->signedQuantityFor($stock->site_id); $other = $signed > 0 ? $m->sourceSite : $m->destinationSite; ?>
                    <tr>
                        <td class="num" style="text-align:left"><?php echo e($m->moved_at->format('d/m/Y H:i')); ?></td>
                        <td class="code"><?php echo e($m->reference); ?></td>
                        <td><span class="tag tag-<?php echo e($m->type->value); ?>"><?php echo e($m->type->label()); ?></span></td>
                        <td><?php echo e($other ? ($signed > 0 ? 'depuis ' : 'vers ').$other->name : '—'); ?></td>
                        <td class="num" style="color: <?php echo e($signed > 0 ? 'var(--pine-deep)' : 'var(--brick)'); ?>"><?php echo e($signed > 0 ? '+' : '−'); ?><?php echo e(Fmt::q(abs($signed))); ?></td>
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

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\stocks\show.blade.php ENDPATH**/ ?>