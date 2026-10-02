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
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-fraunces font-bold text-2xl text-ink leading-tight">
                    <?php echo e(__('Produits')); ?>

                </h2>
                <p class="text-sm text-ink/50 mt-1">Gérez votre catalogue de produits et leur traçabilité.</p>
            </div>
            <a href="<?php echo e(route('admin.products.create')); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-warm hover:bg-amber-light text-white font-semibold rounded-lg transition-all duration-150 shadow-sm shadow-amber-warm/20 hover:shadow-amber-warm/30">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 5V19M5 12H19"/>
                </svg>
                Ajouter un produit
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="mb-6 px-5 py-4 rounded-xl bg-forest/5 border border-forest/15 text-forest text-sm font-medium flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-forest/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M20 6L9 17L4 12"/>
                        </svg>
                    </div>
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden rounded-2xl border border-ink/5 shadow-lg shadow-ink/[0.03]">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink/5">
                        <thead class="bg-cream/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-[11px] font-bold tracking-wider text-ink/50 uppercase">Produit</th>
                                <th class="px-6 py-4 text-left text-[11px] font-bold tracking-wider text-ink/50 uppercase hidden md:table-cell">Code-barres</th>
                                <th class="px-6 py-4 text-left text-[11px] font-bold tracking-wider text-ink/50 uppercase">Empreinte CO₂</th>
                                <th class="px-6 py-4 text-left text-[11px] font-bold tracking-wider text-ink/50 uppercase hidden sm:table-cell">Certifications</th>
                                <th class="px-6 py-4 text-right text-[11px] font-bold tracking-wider text-ink/50 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink/5 bg-white">
                            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-cream/40 transition-colors duration-100">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-11 h-11 rounded-xl bg-cream border border-ink/5 flex items-center justify-center overflow-hidden shrink-0">
                                                <?php if($product->image): ?>
                                                    <img src="<?php echo e(Storage::url($product->image)); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <svg class="w-5 h-5 text-ink/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                                        <path d="M21 15L16 10L5 21"/>
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-sm text-ink truncate max-w-[200px]"><?php echo e($product->name); ?></p>
                                                <p class="text-xs text-ink/40 mt-0.5">
                                                    Par <?php echo e($product->user->name ?? 'Inconnu'); ?>

                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                        <?php if($product->barcode): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-cream text-ink/70 font-mono text-xs border border-ink/5">
                                                <svg class="w-3.5 h-3.5 text-ink/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 5V19M7 5V19M11 5V19M15 5V19M19 5V19"/>
                                                </svg>
                                                <?php echo e($product->barcode); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="text-ink/30 text-xs">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($product->carbon_footprint !== null): ?>
                                            <div class="inline-flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M12 2V22M2 12H22"/>
                                                        <circle cx="12" cy="12" r="10" opacity="0.25"/>
                                                    </svg>
                                                </div>
                                                <span class="font-semibold text-sm text-ink"><?php echo e($product->carbon_footprint); ?> <span class="text-xs text-ink/50 font-normal">kg CO₂</span></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-ink/30 text-xs">Non renseignée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                        <div class="flex flex-wrap gap-1.5">
                                            <?php if($product->is_organic): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-700 border border-emerald-500/15">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M12 2L14.09 8.26L21 9.27L16 14.14L17.18 21.02L12 17.77L6.82 21.02L8 14.14L3 9.27L9.91 8.26L12 2Z"/>
                                                    </svg>
                                                    Bio
                                                </span>
                                            <?php endif; ?>
                                            <?php if($product->is_local): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-warm/10 text-amber-warm border border-amber-warm/15">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <path d="M12 2L12 22M2 12L22 12"/>
                                                        <circle cx="12" cy="12" r="10"/>
                                                    </svg>
                                                    Local
                                                </span>
                                            <?php endif; ?>
                                            <?php if($product->is_fair_trade): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-500/10 text-sky-700 border border-sky-500/15">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M20 7L9 18L4 13"/>
                                                    </svg>
                                                    Équitable
                                                </span>
                                            <?php endif; ?>
                                            <?php if(!$product->is_organic && !$product->is_local && !$product->is_fair_trade): ?>
                                                <span class="text-ink/30 text-xs">Aucune</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="<?php echo e(route('admin.products.show', $product)); ?>" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-ink/50 hover:text-forest hover:bg-forest/10 transition-colors" title="Voir">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                    <path d="M1 12S6 4 12 4S23 12 23 12S18 20 12 20S1 12 1 12Z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </a>
                                            <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-ink/50 hover:text-amber-warm hover:bg-amber-warm/10 transition-colors" title="Modifier">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                    <path d="M12 20H21"/>
                                                    <path d="M16.5 3.5C16.8978 3.10217 17.4374 2.87868 18 2.87868C18.5626 2.87868 19.1022 3.10217 19.5 3.5C19.8978 3.89783 20.1213 4.43743 20.1213 5C20.1213 5.56257 19.8978 6.10217 19.5 6.5L7 19L3 20L4 16L16.5 3.5Z"/>
                                                </svg>
                                            </a>
                                            <form action="<?php echo e(route('admin.products.destroy', $product)); ?>" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-ink/50 hover:text-red-500 hover:bg-red-500/10 transition-colors" title="Supprimer">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                        <path d="M3 6H21"/>
                                                        <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6"/>
                                                        <path d="M19 6L18 20C18 20.5304 17.7893 21.0391 17.4142 21.4142C17.0391 21.7893 16.5304 22 16 22H8C7.46957 22 6.96086 21.7893 6.58579 21.4142C6.21071 21.0391 6 20.5304 6 20L5 6"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 rounded-2xl bg-cream flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-ink/20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                    <path d="M9 9H15M9 13H15M9 17H12"/>
                                                </svg>
                                            </div>
                                            <p class="font-semibold text-ink/70 mb-1">Aucun produit trouvé</p>
                                            <p class="text-sm text-ink/40 mb-5">Commencez par ajouter votre premier produit au catalogue.</p>
                                            <a href="<?php echo e(route('admin.products.create')); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-warm hover:bg-amber-light text-white text-sm font-semibold rounded-lg transition-colors">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <path d="M12 5V19M5 12H19"/>
                                                </svg>
                                                Ajouter un produit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($products->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-ink/5 bg-cream/30">
                        <?php echo e($products->onEachSide(1)->links('pagination::tailwind')); ?>

                    </div>
                <?php endif; ?>
            </div>
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
<?php /**PATH C:\Users\mdain\nutritrace\resources\views/admin/products/index.blade.php ENDPATH**/ ?>