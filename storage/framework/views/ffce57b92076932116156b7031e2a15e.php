<?php $__env->startSection('title', 'Nouveau site'); ?>
<?php $__env->startSection('eyebrow', 'Network workspace'); ?>
<?php $__env->startSection('description', 'Ajoutez un entrepôt, une plateforme ou un magasin au réseau.'); ?>
<?php $__env->startSection('page-action'); ?><a class="btn btn-ghost" href="<?php echo e(route('sites.index')); ?>">Retour aux sites</a><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('sites.store')); ?>" class="panel">
    <?php echo csrf_field(); ?>
    <?php echo $__env->make('sites._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">Créer le site</button>
        <a class="btn btn-ghost" href="<?php echo e(route('sites.index')); ?>">Annuler</a>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\sites\create.blade.php ENDPATH**/ ?>