<?php use \App\Support\Fmt; ?>

<?php $__env->startSection('title', 'Seuil de rupture'); ?>
<?php $__env->startSection('eyebrow', 'Inventory workspace'); ?>
<?php $__env->startSection('description', 'Le seuil s’applique à ce produit sur ce site; les quantités ne changent que par des mouvements.'); ?>
<?php $__env->startSection('page-action'); ?><a class="btn btn-ghost" href="<?php echo e(route('stocks.index', ['site_id' => $stock->site_id])); ?>">Retour aux stocks du site</a><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('stocks.update', $stock)); ?>" class="panel">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div class="form-grid">
        <div class="field">
            <span class="label">Disponible actuellement</span>
            <p style="font-size:1.5rem;font-weight:800" class="num" ><?php echo e(Fmt::q($productAvailable)); ?></p>
        </div>
        <div class="field">
            <label for="min_threshold">Seuil de rupture</label>
            <input type="number" id="min_threshold" name="min_threshold" value="<?php echo e(old('min_threshold', $stock->min_threshold)); ?>" min="0" step="any" required autofocus>
            <?php $__errorArgs = ['min_threshold'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
    </div>
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">Enregistrer le seuil</button>
        <a class="btn btn-ghost" href="<?php echo e(route('stocks.index', ['site_id' => $stock->site_id])); ?>">Annuler</a>
    </div>
</form>

<?php if($stock->quantity <= 0): ?>
    <form method="POST" action="<?php echo e(route('stocks.destroy', $stock)); ?>" onsubmit="return confirm('Supprimer cette ligne vide ?')">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <button class="btn btn-danger" type="submit">Supprimer cette ligne vide</button>
    </form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\stocks\edit.blade.php ENDPATH**/ ?>