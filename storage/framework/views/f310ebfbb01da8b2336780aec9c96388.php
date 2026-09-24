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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm font-semibold uppercase tracking-widest text-amber-warm">Traceability workspace</p><h2 class="mt-1 font-fraunces text-3xl font-bold text-ink">Batch Overview</h2></div><div class="flex flex-wrap gap-2"><a href="<?php echo e(route('admin.batches.traceability', $batch)); ?>" class="rounded-xl bg-amber-warm px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-warm/90">View Traceability</a><a href="<?php echo e(route('admin.batches.edit', $batch)); ?>" class="rounded-xl bg-forest px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest-dark">Edit batch</a><a href="<?php echo e(route('admin.batches.index')); ?>" class="rounded-xl border border-ink/10 px-4 py-2.5 text-sm font-semibold text-ink/65 hover:bg-ink/5">Back</a></div></div>
     <?php $__env->endSlot(); ?>
    <div class="py-10 sm:py-12"><div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-forest p-6 text-white shadow-sm sm:p-8"><div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"><div><p class="text-sm font-medium text-white/60">Lot number</p><h1 class="mt-1 font-fraunces text-3xl font-bold"><?php echo e($batch->lot_number); ?></h1><p class="mt-2 text-white/70"><?php echo e($batch->product?->name ?? 'Product unavailable'); ?></p></div><span class="inline-flex w-fit rounded-full bg-white/15 px-3 py-1.5 text-xs font-bold uppercase tracking-wider"><?php echo e($batch->status); ?></span></div></div>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3"><div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm lg:col-span-2"><h3 class="font-fraunces text-xl font-bold text-ink">Batch Overview</h3><dl class="mt-6 grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2"><div><dt class="text-xs font-semibold uppercase tracking-wider text-ink/45">Product</dt><dd class="mt-1 font-semibold text-ink"><?php echo e($batch->product?->name ?? 'Product unavailable'); ?></dd></div><div><dt class="text-xs font-semibold uppercase tracking-wider text-ink/45">Quantity</dt><dd class="mt-1 font-semibold text-ink"><?php echo e($batch->quantity); ?> <?php echo e($batch->unit); ?></dd></div><div><dt class="text-xs font-semibold uppercase tracking-wider text-ink/45">Production date</dt><dd class="mt-1 text-ink"><?php echo e($batch->production_date?->format('d/m/Y')); ?></dd></div><div><dt class="text-xs font-semibold uppercase tracking-wider text-ink/45">Expiration date</dt><dd class="mt-1 text-ink"><?php echo e($batch->expiration_date?->format('d/m/Y')); ?></dd></div><div><dt class="text-xs font-semibold uppercase tracking-wider text-ink/45">Carbon footprint</dt><dd class="mt-1 text-ink"><?php echo e($batch->carbon_footprint !== null ? $batch->carbon_footprint . ' kg CO₂' : 'Not specified'); ?></dd></div><div><dt class="text-xs font-semibold uppercase tracking-wider text-ink/45">Created</dt><dd class="mt-1 text-ink"><?php echo e($batch->created_at?->format('d/m/Y H:i')); ?></dd></div></dl></div><div class="rounded-2xl border border-dashed border-ink/15 bg-white/70 p-6"><h3 class="font-fraunces text-xl font-bold text-ink">Coming next</h3><p class="mt-2 text-sm leading-6 text-ink/55">This space is ready for the batch's deeper traceability layer.</p><div class="mt-6 space-y-3 text-sm text-ink/60"><div class="rounded-xl bg-cream px-4 py-3">QR Code</div><div class="rounded-xl bg-cream px-4 py-3">Traceability Timeline</div><div class="rounded-xl bg-cream px-4 py-3">Transport events</div><div class="rounded-xl bg-cream px-4 py-3">AI analysis</div></div></div></div>
    </div></div>
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
<?php /**PATH C:\Users\mdain\nutritrace\resources\views\admin\batches\show.blade.php ENDPATH**/ ?>