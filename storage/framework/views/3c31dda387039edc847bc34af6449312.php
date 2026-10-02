<?php if (isset($component)) { $__componentOriginal0de143e5b61900e6d7b990ac144ae3fb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb = $attributes; } ?>
<?php $component = App\View\Components\DashboardLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\DashboardLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-warm">Quality workspace</p><h1 class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">Lancer une analyse</h1><p class="mt-2 text-sm text-ink/55 dark:text-white/55">Importez une image pour obtenir une lecture qualité assistée par Ollama.</p></div>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-3xl">
        <?php if($errors->any()): ?><div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-300/20 dark:bg-rose-400/10 dark:text-rose-300"><p class="font-semibold">Vérifiez les informations saisies.</p><ul class="mt-2 list-disc pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>
        <form action="<?php echo e(route('admin.quality-checks.store')); ?>" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-ink/8 bg-white p-6 shadow-[0_18px_45px_-32px_rgba(31,61,46,0.8)] sm:p-8 dark:border-white/10 dark:bg-[#1E3527]">
            <?php echo csrf_field(); ?>
            <div class="space-y-7">
                <div><label for="product_id" class="block text-sm font-semibold text-ink dark:text-white/85">Produit à analyser</label><select id="product_id" name="product_id" required class="mt-2 block w-full rounded-xl border-ink/10 bg-cream px-4 py-3 text-sm focus:border-amber-warm focus:ring-amber-warm/20 dark:border-white/10 dark:bg-[#16281E] dark:text-white"><option value="">Sélectionner un produit</option><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($product->id); ?>" <?php if(old('product_id') == $product->id): echo 'selected'; endif; ?>><?php echo e($product->name); ?><?php echo e($product->category ? ' · ' . $product->category : ''); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                <div><label for="image" class="block text-sm font-semibold text-ink dark:text-white/85">Image du produit</label><label for="image" class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-forest/20 bg-cream/60 px-6 py-12 text-center transition hover:border-amber-warm hover:bg-amber-warm/5 dark:border-white/15 dark:bg-[#16281E] dark:hover:border-amber-warm"><svg class="h-9 w-9 text-forest dark:text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 16.5V19a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-2.5M12 3v12m0-12 4 4m-4-4-4 4"/></svg><span class="mt-3 text-sm font-semibold text-ink dark:text-white">Déposez une image ici ou cliquez pour parcourir</span><span class="mt-1 text-xs text-ink/50 dark:text-white/45">JPG, PNG ou WEBP · 5 Mo maximum</span><input id="image" name="image" type="file" accept="image/*" required class="sr-only"></label><div id="image-preview" class="mt-4 hidden overflow-hidden rounded-xl border border-ink/10 dark:border-white/10"><img alt="Aperçu de l'image" class="max-h-72 w-full object-contain"></div></div>
                <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-900 dark:border-amber-300/20 dark:bg-amber-400/10 dark:text-amber-200"><p class="font-semibold">Analyse en arrière-plan</p><p class="mt-1 leading-6">Ollama analyse l’image après l’envoi. Vous pourrez suivre le statut et le résultat depuis la page de détail.</p></div>
            </div>
            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-ink/8 pt-6 sm:flex-row sm:items-center sm:justify-end dark:border-white/10"><a href="<?php echo e(route('admin.quality-checks.index')); ?>" class="rounded-xl px-5 py-3 text-center text-sm font-semibold text-ink/60 hover:bg-ink/5 dark:text-white/60 dark:hover:bg-white/5">Annuler</a><button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-forest-dark"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M3 12h18"/></svg>Lancer l'analyse IA</button></div>
        </form>
    </div>
    <script>document.getElementById('image')?.addEventListener('change', (event) => { const file = event.target.files?.[0]; const preview = document.getElementById('image-preview'); if (!file || !preview) return; preview.classList.remove('hidden'); preview.querySelector('img').src = URL.createObjectURL(file); });</script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb)): ?>
<?php $attributes = $__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb; ?>
<?php unset($__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0de143e5b61900e6d7b990ac144ae3fb)): ?>
<?php $component = $__componentOriginal0de143e5b61900e6d7b990ac144ae3fb; ?>
<?php unset($__componentOriginal0de143e5b61900e6d7b990ac144ae3fb); ?>
<?php endif; ?>
<?php /**PATH C:\Users\mdain\nutritrace\resources\views\admin\quality-checks\create.blade.php ENDPATH**/ ?>