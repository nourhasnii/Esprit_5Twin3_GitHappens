<?php echo csrf_field(); ?>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-white/80">Nom du produit</label>
        <input id="name" type="text" name="name" value="<?php echo e(old('name', $product->name ?? '')); ?>" required class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-[#E3A23C] focus:ring-[#E3A23C]/30 dark:border-white/10 dark:bg-[#1E3527] dark:text-white dark:placeholder:text-white/40">
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-white/80">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-[#E3A23C] focus:ring-[#E3A23C]/30 dark:border-white/10 dark:bg-[#1E3527] dark:text-white dark:placeholder:text-white/40"><?php echo e(old('description', $product->description ?? '')); ?></textarea>
        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-white/80">Catégorie</label>
        <?php if($categories->isNotEmpty()): ?>
            <select id="category_id" name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-[#E3A23C] focus:ring-[#E3A23C]/30 dark:border-white/10 dark:bg-[#1E3527] dark:text-white">
                <option value="">Sélectionner une catégorie</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php if((string) old('category_id', $product->category_id ?? '') === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?><?php echo e(! $category->is_active ? ' · inactive' : ''); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if(isset($product) && ! $product->category_id && $product->category): ?>
                <p class="mt-1 text-xs text-ink/50 dark:text-white/50">Catégorie historique : <?php echo e($product->category); ?>. Sélectionnez une catégorie pour associer le produit.</p>
            <?php endif; ?>
            <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php else: ?>
            <input id="category" type="text" name="category" value="<?php echo e(old('category', $product->category ?? '')); ?>" required class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-[#E3A23C] focus:ring-[#E3A23C]/30 dark:border-white/10 dark:bg-[#1E3527] dark:text-white">
            <p class="mt-1 text-xs text-ink/50 dark:text-white/50">Aucune catégorie active. La catégorie historique sera conservée.</p>
            <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php endif; ?>
    </div>

    <div>
        <label for="unit" class="block text-sm font-medium text-gray-700">Unité</label>
        <input id="unit" type="text" name="unit" value="<?php echo e(old('unit', $product->unit ?? 'kg')); ?>" required placeholder="kg, litre, pièce..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <?php $__errorArgs = ['unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="origin_country" class="block text-sm font-medium text-gray-700">Pays d'origine</label>
        <input id="origin_country" type="text" name="origin_country" value="<?php echo e(old('origin_country', $product->origin_country ?? '')); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <?php $__errorArgs = ['origin_country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="origin_region" class="block text-sm font-medium text-gray-700">Région d'origine</label>
        <input id="origin_region" type="text" name="origin_region" value="<?php echo e(old('origin_region', $product->origin_region ?? '')); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <?php $__errorArgs = ['origin_region'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="producer_id" class="block text-sm font-medium text-gray-700">Producteur</label>
        <select id="producer_id" name="producer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Sélectionner un producteur</option>
            <?php $__currentLoopData = $producers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($producer->id); ?>" <?php if((string) old('producer_id', $product->producer_id ?? '') === (string) $producer->id): echo 'selected'; endif; ?>><?php echo e($producer->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['producer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="barcode" class="block text-sm font-medium text-gray-700">Code-barres</label>
        <input id="barcode" type="text" name="barcode" value="<?php echo e(old('barcode', $product->barcode ?? '')); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <?php $__errorArgs = ['barcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="image" class="block text-sm font-medium text-gray-700">Image (URL)</label>
        <input id="image" type="url" name="image" value="<?php echo e(old('image', $product->image ?? '')); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="carbon_footprint" class="block text-sm font-medium text-gray-700">Empreinte carbone</label>
        <input id="carbon_footprint" type="number" step="0.01" min="0" name="carbon_footprint" value="<?php echo e(old('carbon_footprint', $product->carbon_footprint ?? '')); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <?php $__errorArgs = ['carbon_footprint'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="verification_status" class="block text-sm font-medium text-gray-700">Statut de vérification</label>
        <select id="verification_status" name="verification_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <?php $__currentLoopData = ['pending' => 'En attente', 'verified' => 'Vérifié', 'rejected' => 'Rejeté']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('verification_status', $product->verification_status ?? 'pending') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['verification_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <label class="flex items-center gap-3 md:col-span-2">
        <input type="checkbox" name="is_organic" value="1" <?php if(old('is_organic', $product->is_organic ?? false)): echo 'checked'; endif; ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <span class="text-sm font-medium text-gray-700">Produit biologique</span>
        <?php $__errorArgs = ['is_organic'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="field-validation-error text-xs text-red-500"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </label>
</div>

<div class="mt-8 flex items-center justify-end gap-4">
    <a href="<?php echo e(route('admin.products.index')); ?>" class="text-ink/65 hover:text-forest">Annuler</a>
    <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"><?php echo e($submitLabel); ?></button>
</div>
<?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\admin\products\_form.blade.php ENDPATH**/ ?>