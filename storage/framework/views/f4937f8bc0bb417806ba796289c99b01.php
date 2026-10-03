<?php use \App\Support\Fmt; ?>
<?php use \App\Support\BatchAttributes; ?>

<?php $__env->startSection('eyebrow', 'Optimization workspace'); ?>
<?php $__env->startSection('title', 'Recommandations'); ?>
<?php $__env->startSection('description', 'Classez les destinations selon la demande, la DLC, la distance, la capacité et le CO₂; chaque proposition reste soumise à votre validation.'); ?>
<?php $__env->startSection('page-action'); ?>
    <a class="btn btn-primary" href="<?php echo e(route('optimization.create')); ?>">Trouver une destination</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.impact', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<section class="panel">
    <?php if($recommendations->isEmpty()): ?>
        <div class="empty">
            <p>Aucune recommandation pour l’instant. Choisissez un lot en stock pour obtenir sa meilleure destination.</p>
            <a class="btn btn-primary" href="<?php echo e(route('optimization.create')); ?>">Trouver une destination pour un lot</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Demandée le</th>
                    <th>Lot</th>
                    <th>Origine</th>
                    <th>Destination recommandée</th>
                    <th class="num">Score</th>
                    <th class="num">CO₂</th>
                    <th>Décision</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="white-space:nowrap"><?php echo e($r->created_at->format('d/m/Y H:i')); ?></td>
                        <td>
                            <span class="code"><?php echo e(BatchAttributes::code($r->batch)); ?></span>
                            <div class="muted small"><?php echo e($r->product?->name); ?>, <?php echo e(Fmt::q($r->quantity)); ?> u.</div>
                        </td>
                        <td><?php echo e($r->sourceSite?->name ?? '—'); ?></td>
                        <td>
                            <?php echo e($r->recommendedSite?->name ?? 'Aucune destination faisable'); ?>

                            <?php if($r->chosen_site_id && $r->chosen_site_id !== $r->recommended_site_id): ?>
                                <div class="muted small">Retenu : <?php echo e($r->chosenSite?->name); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="num"><b><?php echo e(Fmt::n($r->score)); ?></b>/100</td>
                        <td class="num"><?php echo e($r->co2_kg !== null ? Fmt::n($r->co2_kg, 1).' kg' : '—'); ?></td>
                        <td><span class="tag tag-<?php echo e($r->status->value); ?>"><?php echo e($r->status->label()); ?></span></td>
                        <td class="row-actions"><a href="<?php echo e(route('optimization.show', $r)); ?>">Ouvrir</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<div class="pager"><?php echo e($recommendations->links('pagination::default')); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views/optimization/index.blade.php ENDPATH**/ ?>