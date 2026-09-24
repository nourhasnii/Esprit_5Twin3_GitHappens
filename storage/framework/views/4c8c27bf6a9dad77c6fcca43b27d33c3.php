<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('front.products.index')); ?>" class="text-sm font-semibold text-forest hover:text-amber-warm">Products</a>
            <span class="text-ink/30">/</span>
            <h1 class="truncate font-fraunces text-2xl font-semibold text-ink"><?php echo e($product->name); ?></h1>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="bg-cream py-10 sm:py-14">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-ink/8 bg-white shadow-sm">
                <div class="grid lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="flex min-h-72 items-center justify-center bg-forest/5 lg:min-h-full">
                        <?php if($product->image): ?>
                            <img src="<?php echo e($product->image); ?>" alt="<?php echo e($product->name); ?>" class="h-full max-h-[30rem] w-full object-cover">
                        <?php else: ?>
                            <span class="font-fraunces text-8xl font-semibold text-forest/25"><?php echo e(strtoupper(substr($product->name, 0, 1))); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="p-6 sm:p-10">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-forest/10 px-3 py-1 text-xs font-semibold text-forest"><?php echo e($product->category); ?></span>
                            <?php if($product->verification_status === 'verified'): ?>
                                <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700">Verified product</span>
                            <?php endif; ?>
                        </div>
                        <h2 class="mt-5 font-fraunces text-4xl font-semibold text-ink"><?php echo e($product->name); ?></h2>
                        <p class="mt-4 leading-7 text-ink/65"><?php echo e($product->description ?: 'No description has been provided for this product yet.'); ?></p>

                        <dl class="mt-8 grid gap-5 border-t border-ink/8 pt-6 sm:grid-cols-2">
                            <div><dt class="text-xs uppercase tracking-wider text-ink/45">Origin</dt><dd class="mt-1 font-semibold text-ink"><?php echo e(collect([$product->origin_region, $product->origin_country])->filter()->join(', ') ?: 'Not specified'); ?></dd></div>
                            <div><dt class="text-xs uppercase tracking-wider text-ink/45">Producer</dt><dd class="mt-1 font-semibold text-ink"><?php echo e($product->producer?->name ?: 'Not specified'); ?></dd></div>
                            <div><dt class="text-xs uppercase tracking-wider text-ink/45">Unit</dt><dd class="mt-1 font-semibold text-ink"><?php echo e($product->unit ?: 'Not specified'); ?></dd></div>
                            <div><dt class="text-xs uppercase tracking-wider text-ink/45">Lots tracked</dt><dd class="mt-1 font-semibold text-ink"><?php echo e($product->batches->count()); ?></dd></div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-ink/8 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Traceability</p><h2 class="mt-1 font-fraunces text-2xl font-semibold text-ink">Production lots</h2></div><span class="text-sm text-ink/50"><?php echo e($product->batches->count()); ?> total</span></div>
                <div class="mt-5 divide-y divide-ink/8">
                    <?php $__empty_1 = true; $__currentLoopData = $product->batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex flex-col justify-between gap-2 py-4 sm:flex-row sm:items-center"><div><p class="font-semibold text-ink"><?php echo e($batch->lot_number); ?></p><p class="text-sm text-ink/55">Produced <?php echo e($batch->production_date?->format('d M Y') ?: '—'); ?></p></div><span class="rounded-full bg-forest/10 px-3 py-1 text-xs font-semibold text-forest"><?php echo e(ucfirst($batch->status)); ?></span></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="py-8 text-center text-sm text-ink/50">No production lots have been recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\mdain\nutritrace\resources\views\front\products\show.blade.php ENDPATH**/ ?>