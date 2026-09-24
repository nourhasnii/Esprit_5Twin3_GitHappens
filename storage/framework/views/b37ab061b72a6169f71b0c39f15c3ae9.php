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
     <?php $__env->slot('header', null, []); ?> <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Account status</p><h1 class="mt-1 font-fraunces text-2xl font-semibold text-ink dark:text-white">Your application is under review</h1> <?php $__env->endSlot(); ?>
    <div class="mx-auto max-w-2xl rounded-2xl border border-amber-warm/20 bg-white p-8 shadow-sm dark:border-amber-300/20 dark:bg-[#1b2923]"><div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-warm/10 text-amber-warm"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><h2 class="mt-6 font-fraunces text-2xl font-semibold text-ink dark:text-white"><?php echo e($user->account_status === 'information_required' ? 'Additional information required' : 'Thanks for registering'); ?></h2><p class="mt-3 text-sm leading-6 text-ink/60 dark:text-white/60"><?php echo e($user->account_status === 'information_required' ? 'Our team needs a little more information before we can verify your professional account.' : 'Your professional account has been received and is being reviewed by the NutriTrace team. We will email you when a decision is ready.'); ?></p><?php if($user->rejection_reason): ?><p class="mt-5 rounded-xl bg-rose-500/10 p-4 text-sm text-rose-700 dark:text-rose-300"><?php echo e($user->rejection_reason); ?></p><?php endif; ?></div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb)): ?>
<?php $attributes = $__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb; ?>
<?php unset($__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0de143e5b61900e6d7b990ac144ae3fb)): ?>
<?php $component = $__componentOriginal0de143e5b61900e6d7b990ac144ae3fb; ?>
<?php unset($__componentOriginal0de143e5b61900e6d7b990ac144ae3fb); ?>
<?php endif; ?><?php /**PATH C:\Users\mdain\nutritrace\resources\views\account\pending.blade.php ENDPATH**/ ?>