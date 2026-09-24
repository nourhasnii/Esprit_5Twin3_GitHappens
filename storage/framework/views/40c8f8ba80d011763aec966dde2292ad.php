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
     <?php $__env->slot('header', null, []); ?> <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Administration</p><h1 class="mt-1 font-fraunces text-2xl font-semibold text-ink dark:text-white">Audit Logs</h1> <?php $__env->endSlot(); ?>
    <div class="mx-auto max-w-[1200px] rounded-2xl border border-ink/8 bg-white shadow-sm dark:border-white/10 dark:bg-[#1b2923]"><form class="border-b border-ink/8 p-5 dark:border-white/10"><input name="search" value="<?php echo e(request('search')); ?>" placeholder="Search actions" class="w-full rounded-xl border-ink/10 bg-cream text-sm dark:border-white/10 dark:bg-white/5"></form><div class="divide-y divide-ink/8 dark:divide-white/10"><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="grid gap-2 p-5 sm:grid-cols-[1fr_1fr_180px_120px]"><div><p class="font-semibold dark:text-white"><?php echo e($log->action); ?></p><p class="text-xs text-ink/50 dark:text-white/50"><?php echo e($log->actor?->name ?? 'System'); ?></p></div><p class="text-sm text-ink/60 dark:text-white/60"><?php echo e(class_basename($log->entity_type ?? 'System')); ?> #<?php echo e($log->entity_id ?: '—'); ?></p><p class="text-xs text-ink/50 dark:text-white/50"><?php echo e($log->created_at?->format('d M Y, H:i')); ?></p><p class="text-xs text-ink/50 dark:text-white/50"><?php echo e($log->ip_address ?: '—'); ?></p></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="p-10 text-center text-sm text-ink/50">No audit records yet.</p><?php endif; ?></div><div class="p-5"><?php echo e($logs->links()); ?></div></div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb)): ?>
<?php $attributes = $__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb; ?>
<?php unset($__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0de143e5b61900e6d7b990ac144ae3fb)): ?>
<?php $component = $__componentOriginal0de143e5b61900e6d7b990ac144ae3fb; ?>
<?php unset($__componentOriginal0de143e5b61900e6d7b990ac144ae3fb); ?>
<?php endif; ?><?php /**PATH C:\Users\mdain\nutritrace\resources\views\admin\audit\index.blade.php ENDPATH**/ ?>