<?php
    $statusClasses = [
        'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300',
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-400/15 dark:text-amber-300',
        'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-400/15 dark:text-rose-300',
    ];
    $decisionClasses = [
        'sell' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300',
        'donate' => 'bg-sky-100 text-sky-800 dark:bg-sky-400/15 dark:text-sky-300',
        'withdraw' => 'bg-rose-100 text-rose-800 dark:bg-rose-400/15 dark:text-rose-300',
        'inspect' => 'bg-amber-100 text-amber-800 dark:bg-amber-400/15 dark:text-amber-300',
    ];
    $decisionLabels = ['sell' => 'Vente', 'donate' => 'Don', 'withdraw' => 'Retrait', 'inspect' => 'Contrôle'];
    $collection = $checks->getCollection();
?>
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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-warm">Quality workspace</p>
                <h1 class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">Analyses Qualité</h1>
                <p class="mt-2 text-sm text-ink/55 dark:text-white/55">Évaluez la fraîcheur et la qualité des produits avec l'analyse visuelle.</p>
            </div>
            <a href="<?php echo e(route('admin.quality-checks.create')); ?>" class="inline-flex items-center justify-center gap-2 rounded-xl bg-forest px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-dark">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nouvelle analyse
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-7xl space-y-6">
        <?php if(session('success')): ?>
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-300/20 dark:bg-emerald-400/10 dark:text-emerald-300"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <?php $__currentLoopData = [
                ['label' => 'Total analyses', 'value' => $checks->total(), 'tone' => 'forest'],
                ['label' => 'Completed', 'value' => $collection->where('status', 'completed')->count(), 'tone' => 'emerald'],
                ['label' => 'Pending', 'value' => $collection->where('status', 'pending')->count(), 'tone' => 'amber'],
                ['label' => 'Failed', 'value' => $collection->where('status', 'failed')->count(), 'tone' => 'rose'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-2xl border border-ink/8 bg-white p-5 shadow-[0_14px_35px_-28px_rgba(31,61,46,0.7)] dark:border-white/10 dark:bg-[#1E3527]">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45"><?php echo e($summary['label']); ?></p>
                    <p class="mt-2 font-fraunces text-3xl font-bold <?php echo e($summary['tone'] === 'forest' ? 'text-ink dark:text-white' : 'text-' . $summary['tone'] . '-700 dark:text-' . $summary['tone'] . '-300'); ?>"><?php echo e($summary['value']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="overflow-hidden rounded-2xl border border-ink/8 bg-white shadow-sm dark:border-white/10 dark:bg-[#1E3527]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink/8 dark:divide-white/10">
                    <thead class="bg-cream/70 dark:bg-[#16281E]"><tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Produit</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Score</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Décision</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Confiance</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-ink/8 dark:divide-white/10">
                        <?php $__empty_1 = true; $__currentLoopData = $checks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="transition hover:bg-cream/45 dark:hover:bg-white/5">
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-cream ring-1 ring-ink/8 dark:bg-[#16281E] dark:ring-white/10"><?php if($check->image_path): ?><img src="<?php echo e(asset('storage/' . $check->image_path)); ?>" alt="<?php echo e($check->product?->name ?? 'Produit'); ?>" class="h-full w-full object-cover"><?php else: ?><span class="text-lg text-forest dark:text-emerald-300">◎</span><?php endif; ?></div><div class="min-w-0"><p class="truncate font-semibold text-ink dark:text-white"><?php echo e($check->product?->name ?? 'Produit supprimé'); ?></p><p class="text-xs text-ink/45 dark:text-white/45">Analyse #<?php echo e($check->id); ?></p></div></div></td>
                                <td class="px-6 py-4"><div class="w-28"><div class="flex items-center justify-between gap-2"><span class="font-fraunces text-lg font-bold text-ink dark:text-white"><?php echo e($check->quality_score !== null ? number_format((float) $check->quality_score, 1) : '—'); ?></span><span class="text-[10px] text-ink/45 dark:text-white/45">/100</span></div><div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-ink/10 dark:bg-white/10"><div class="h-full rounded-full bg-forest" style="width: <?php echo e(min(max((float) ($check->quality_score ?? 0), 0), 100)); ?>%"></div></div></div></td>
                                <td class="px-6 py-4"><span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold <?php echo e($decisionClasses[$check->decision] ?? 'bg-ink/10 text-ink/60 dark:bg-white/10 dark:text-white/60'); ?>"><?php echo e($decisionLabels[$check->decision] ?? 'En attente'); ?></span></td>
                                <td class="px-6 py-4 text-sm font-medium text-ink/65 dark:text-white/65"><?php echo e($check->confidence !== null ? number_format((float) $check->confidence, 1) . '%' : '—'); ?></td>
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold <?php echo e($statusClasses[$check->status] ?? 'bg-ink/10 text-ink/60 dark:bg-white/10 dark:text-white/60'); ?>"><span class="h-1.5 w-1.5 rounded-full bg-current"></span><?php echo e(ucfirst($check->status)); ?></span></td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-ink/55 dark:text-white/55"><?php echo e($check->created_at?->format('d/m/Y H:i')); ?></td>
                                <td class="whitespace-nowrap px-6 py-4 text-right"><div class="flex items-center justify-end gap-3"><a href="<?php echo e(route('admin.quality-checks.show', $check)); ?>" class="rounded-lg px-2.5 py-1.5 font-semibold text-forest transition hover:bg-forest/10 hover:text-amber-warm dark:text-emerald-300 dark:hover:bg-emerald-300/10">Voir</a><form action="<?php echo e(route('admin.quality-checks.destroy', $check)); ?>" method="POST" class="inline" onsubmit="return confirm('Supprimer cette analyse ?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="rounded-lg px-2.5 py-1.5 font-semibold text-rose-700 transition hover:bg-rose-50 hover:text-rose-500 dark:text-rose-300 dark:hover:bg-rose-400/10">Supprimer</button></form></div></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="px-6 py-16 text-center"><div class="mx-auto max-w-md"><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-forest/10 text-forest dark:bg-emerald-400/10 dark:text-emerald-300">✦</div><h3 class="mt-4 font-fraunces text-xl font-bold text-ink dark:text-white">Aucune analyse pour le moment</h3><p class="mt-2 text-sm text-ink/55 dark:text-white/55">Lancez une première analyse visuelle pour obtenir un score qualité.</p><a href="<?php echo e(route('admin.quality-checks.create')); ?>" class="mt-5 inline-flex rounded-xl bg-amber-warm px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest">Nouvelle analyse</a></div></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($checks->hasPages()): ?><div class="border-t border-ink/8 p-5 dark:border-white/10"><?php echo e($checks->links()); ?></div><?php endif; ?>
        </div>
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
<?php endif; ?>
<?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views/admin/quality-checks/index.blade.php ENDPATH**/ ?>