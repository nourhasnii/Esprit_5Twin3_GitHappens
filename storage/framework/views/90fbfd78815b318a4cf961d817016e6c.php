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
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Administration / Users</p>
        <h1 class="mt-1 font-fraunces text-2xl font-semibold text-ink dark:text-white"><?php echo e($user->name); ?></h1>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto grid max-w-[1100px] gap-6 lg:grid-cols-[1.1fr_.9fr]">
        <section class="space-y-6">
            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Profile</p>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <?php $__currentLoopData = [
                        ['Name', $user->name],
                        ['Email', $user->email],
                        ['Role', ucfirst($user->getRoleNames()->first() ?: '—')],
                        ['Registered', $user->created_at?->format('d M Y, H:i')],
                        ['Phone', $user->phone ?: '—'],
                        ['Status', str_replace('_', ' ', ucfirst($user->account_status))],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <p class="text-xs text-ink/45 dark:text-white/45"><?php echo e($label); ?></p>
                            <p class="mt-1 font-semibold dark:text-white"><?php echo e($value); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Organization</p>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <?php $__currentLoopData = [
                        ['Organization', $user->organization_name ?: '—'],
                        ['Identifier', $user->professional_identifier ?: '—'],
                        ['Location', collect([$user->region, $user->country])->filter()->join(', ') ?: '—'],
                        ['Address', $user->address ?: '—'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <p class="text-xs text-ink/45 dark:text-white/45"><?php echo e($label); ?></p>
                            <p class="mt-1 dark:text-white"><?php echo e($value); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Account controls</p>

                <form method="POST" action="<?php echo e(route('admin.users.status', $user)); ?>" class="mt-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="account_status" value="<?php echo e($user->account_status === 'suspended' ? 'active' : 'suspended'); ?>">
                    <button class="rounded-xl border border-ink/10 px-4 py-2.5 text-sm font-semibold dark:border-white/10 dark:text-white">
                        <?php echo e($user->account_status === 'suspended' ? 'Reactivate account' : 'Suspend account'); ?>

                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Verification</p>
                <p class="mt-3 font-fraunces text-2xl font-semibold dark:text-white">
                    <?php echo e(str_replace('_', ' ', ucfirst($user->account_status))); ?>

                </p>

                <?php if($user->verificationRequests->last()): ?>
                    <p class="mt-2 text-sm text-ink/55 dark:text-white/55">
                        Submitted <?php echo e($user->verificationRequests->last()->submitted_at?->diffForHumans()); ?>

                    </p>

                    <?php if(in_array($user->account_status, ['pending', 'information_required'], true)): ?>
                        <a href="<?php echo e(route('admin.verification.show', $user->verificationRequests->last())); ?>" class="mt-5 inline-flex rounded-xl bg-forest px-4 py-2.5 text-sm font-semibold text-white">
                            Review verification
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">Previous decisions</p>

                <div class="mt-4 space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $user->verificationRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border-b border-ink/8 pb-3 last:border-0 dark:border-white/10">
                            <p class="text-sm font-semibold dark:text-white">
                                <?php echo e(ucfirst(str_replace('_', ' ', $request->status))); ?>

                            </p>
                            <p class="text-xs text-ink/50 dark:text-white/50">
                                <?php echo e($request->reviewed_at?->format('d M Y') ?: 'Submitted ' . $request->submitted_at?->format('d M Y')); ?>

                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-ink/50">No verification requests.</p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb)): ?>
<?php $attributes = $__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb; ?>
<?php unset($__attributesOriginal0de143e5b61900e6d7b990ac144ae3fb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0de143e5b61900e6d7b990ac144ae3fb)): ?>
<?php $component = $__componentOriginal0de143e5b61900e6d7b990ac144ae3fb; ?>
<?php unset($__componentOriginal0de143e5b61900e6d7b990ac144ae3fb); ?>
<?php endif; ?><?php /**PATH C:\Users\mdain\nutritrace\resources\views\admin\users\show.blade.php ENDPATH**/ ?>