<?php use \App\Support\BatchAttributes; ?>
<?php use \App\Enums\StockMovementType; ?>

<?php $__env->startSection('title', 'Enregistrer un mouvement'); ?>
<?php $__env->startSection('eyebrow', 'Inventory workspace'); ?>
<?php $__env->startSection('description', 'Chaque mouvement met à jour immédiatement le stock du ou des sites concernés.'); ?>
<?php $__env->startSection('page-action'); ?><a class="btn btn-ghost" href="<?php echo e(route('stock-movements.index')); ?>">Retour aux mouvements</a><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<form method="POST" action="<?php echo e(route('stock-movements.store')); ?>" class="panel" id="movement-form">
    <?php echo csrf_field(); ?>
    <fieldset class="choices">
        <legend>Type de mouvement</legend>
        <?php $__currentLoopData = StockMovementType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label class="choice">
                <input type="radio" name="type" value="<?php echo e($type->value); ?>" <?php if(old('type', $defaultType->value) === $type->value): echo 'checked'; endif; ?>>
                <b><?php echo e($type->label()); ?></b>
                <small><?php echo e($type->description()); ?></small>
            </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </fieldset>
    <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

    <div class="form-grid">
        <div class="field">
            <label for="batch_id">Lot</label>
            <select id="batch_id" name="batch_id">
                <option value="">Sans lot</option>
                <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($batch->id); ?>" data-product="<?php echo e($batch->product_id); ?>" <?php if(old('batch_id') == $batch->id): echo 'selected'; endif; ?>>
                        <?php echo e(BatchAttributes::code($batch)); ?>, <?php echo e($batch->product?->name); ?>

                        <?php if($d = BatchAttributes::expiryDate($batch)): ?> (DLC <?php echo e($d->format('d/m/Y')); ?>) <?php endif; ?>
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
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
            <p class="hint">Rempli automatiquement quand un lot est choisi.</p>
            <?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="field" data-show="out transfer">
            <label for="source_site_id">Site d’origine</label>
            <select id="source_site_id" name="source_site_id">
                <option value="">Choisir un site</option>
                <?php $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($site->id); ?>" <?php if(old('source_site_id') == $site->id): echo 'selected'; endif; ?>><?php echo e($site->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['source_site_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="field" data-show="in transfer">
            <label for="destination_site_id">Site de destination</label>
            <select id="destination_site_id" name="destination_site_id">
                <option value="">Choisir un site</option>
                <?php $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($site->id); ?>" <?php if(old('destination_site_id') == $site->id): echo 'selected'; endif; ?>><?php echo e($site->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['destination_site_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="field" data-show="adjustment">
            <label for="site_id">Site inventorié</label>
            <select id="site_id" name="site_id">
                <option value="">Choisir un site</option>
                <?php $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($site->id); ?>" <?php if(old('site_id') == $site->id): echo 'selected'; endif; ?>><?php echo e($site->name); ?></option>
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

        <div class="field" data-show="in out transfer">
            <label for="quantity">Quantité</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo e(old('quantity')); ?>" min="0.01" step="any">
            <?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="field" data-show="adjustment">
            <label for="counted_quantity">Quantité comptée</label>
            <input type="number" id="counted_quantity" name="counted_quantity" value="<?php echo e(old('counted_quantity')); ?>" min="0" step="any">
            <p class="hint">Saisissez ce que vous avez réellement compté ; l’écart avec le stock enregistré est calculé automatiquement.</p>
            <?php $__errorArgs = ['counted_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="field">
            <label for="moved_at">Date du mouvement</label>
            <input type="datetime-local" id="moved_at" name="moved_at" value="<?php echo e(old('moved_at', now()->format('Y-m-d\TH:i'))); ?>" max="<?php echo e(now()->format('Y-m-d\TH:i')); ?>">
            <?php $__errorArgs = ['moved_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="field">
            <label for="reason">Motif</label>
            <input type="text" id="reason" name="reason" value="<?php echo e(old('reason')); ?>" maxlength="255" placeholder="Ventes du jour, casse, réassort…">
            <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="field wide">
            <label for="notes">Commentaire</label>
            <textarea id="notes" name="notes" maxlength="2000"><?php echo e(old('notes')); ?></textarea>
            <?php $__errorArgs = ['notes'];
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
        <button class="btn btn-primary" type="submit">Enregistrer le mouvement</button>
        <a class="btn btn-ghost" href="<?php echo e(route('stock-movements.index')); ?>">Annuler</a>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (() => {
        const form = document.getElementById('movement-form');
        const sync = () => {
            const type = form.querySelector('input[name="type"]:checked')?.value;
            form.querySelectorAll('[data-show]').forEach((field) => {
                const visible = field.dataset.show.split(' ').includes(type);
                field.hidden = !visible;
                field.querySelectorAll('input, select, textarea').forEach((el) => { el.disabled = !visible; });
            });
        };
        form.querySelectorAll('input[name="type"]').forEach((radio) => radio.addEventListener('change', sync));
        sync();

        const batch = document.getElementById('batch_id');
        const product = document.getElementById('product_id');
        batch.addEventListener('change', () => {
            const id = batch.selectedOptions[0]?.dataset.product;
            if (id) product.value = id;
        });
    })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\stock-movements\create.blade.php ENDPATH**/ ?>