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
        <div><p class="text-sm font-semibold uppercase tracking-widest text-amber-warm">Traceability workspace</p><h2 class="mt-1 font-fraunces text-3xl font-bold text-ink">Add Batch</h2></div>
     <?php $__env->endSlot(); ?>
    <div class="py-10 sm:py-12"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8"><div class="mb-8"><p class="max-w-2xl text-sm leading-6 text-ink/60">Créez un nouveau lot et documentez son parcours dès sa production.</p></div><div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm sm:p-8"><form action="<?php echo e(route('admin.batches.store')); ?>" method="POST"><?php echo $__env->make('admin.batches._form', ['submitLabel' => 'Create batch'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></form></div></div></div>
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
<?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\admin\batches\create.blade.php ENDPATH**/ ?>