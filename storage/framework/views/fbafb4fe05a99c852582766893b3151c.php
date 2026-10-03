<?php use \App\Support\BatchAttributes; ?>

<?php $__env->startSection('title', 'Nouvelle ligne de stock'); ?>
<?php $__env->startSection('eyebrow', 'Inventory workspace'); ?>
<?php $__env->startSection('description', 'Enregistrez le stock initial; il sera ajouté à l’historique comme un mouvement d’entrée.'); ?>
<?php $__env->startSection('page-action'); ?><a class="btn btn-ghost" href="<?php echo e(route('stocks.index')); ?>">Retour aux stocks</a><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('stocks.store')); ?>" class="panel">
    <?php echo csrf_field(); ?>
    <div class="form-grid">
        <div class="field">
            <label for="site_id">Site</label>
            <select id="site_id" name="site_id" required>
                <option value="">Choisir un site</option>
                <?php $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($site->id); ?>" <?php if(old('site_id', $selectedSite) == $site->id): echo 'selected'; endif; ?>><?php echo e($site->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['site_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="field">
            <label for="batch_id">Lot</label>
            <select id="batch_id" name="batch_id">
                <option value="">Sans lot (suivi par produit)</option>
                <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($batch->id); ?>" data-product="<?php echo e($batch->product_id); ?>" <?php if(old('batch_id') == $batch->id): echo 'selected'; endif; ?>>
                        <?php echo e(BatchAttributes::code($batch)); ?>, <?php echo e($batch->product?->name); ?>

                        <?php if($d = BatchAttributes::expiryDate($batch)): ?> (DLC <?php echo e($d->format('d/m/Y')); ?>) <?php endif; ?>
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <p class="hint">Si vous choisissez un lot, son produit est utilisé automatiquement.</p>
            <?php $__errorArgs = ['batch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="field">
            <label for="product_id">Produit</label>
            <select id="product_id" name="product_id">
                <option value="">Choisir un produit</option>
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($product->id); ?>" <?php if(old('product_id') == $product->id): echo 'selected'; endif; ?>><?php echo e($product->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="field">
            <label for="quantity">Quantité initiale</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo e(old('quantity', 0)); ?>" min="0" step="any" required>
            <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="field">
            <label for="min_threshold">Seuil de rupture</label>
            <input type="number" id="min_threshold" name="min_threshold" value="<?php echo e(old('min_threshold', 0)); ?>" min="0" step="any" required>
            <p class="hint">Une alerte est envoyée quand le stock disponible du produit sur ce site passe sous ce niveau. 0 désactive l’alerte.</p>
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
        <button class="btn btn-primary" type="submit">Créer la ligne de stock</button>
        <a class="btn btn-ghost" href="<?php echo e(route('stocks.index')); ?>">Annuler</a>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (() => {
        const batch = document.getElementById('batch_id');
        const product = document.getElementById('product_id');
        batch.addEventListener('change', () => {
            const id = batch.selectedOptions[0]?.dataset.product;
            if (id) product.value = id;
        });
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\stocks\create.blade.php ENDPATH**/ ?>