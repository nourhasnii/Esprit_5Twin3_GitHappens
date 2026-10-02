<?php
    $score = $ocrCheck->inconsistency_score !== null ? (float) $ocrCheck->inconsistency_score : null;
    $scoreTone = $score === null
        ? ['label' => '—', 'text' => 'text-ink dark:text-white', 'accent' => 'accent-ink']
        : ($score <= 20
            ? ['label' => 'Faible', 'text' => 'text-emerald-700 dark:text-emerald-300', 'accent' => 'accent-emerald-500']
            : ($score <= 50
                ? ['label' => 'Modéré', 'text' => 'text-amber-700 dark:text-amber-300', 'accent' => 'accent-amber-500']
                : ['label' => 'Élevé', 'text' => 'text-rose-700 dark:text-rose-300', 'accent' => 'accent-rose-500']));
    $statusClasses = [
        'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300',
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-400/15 dark:text-amber-300',
        'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-400/15 dark:text-rose-300',
    ];
    $severityClasses = [
        'critical' => 'bg-rose-100 text-rose-800 dark:bg-rose-400/15 dark:text-rose-300',
        'moderate' => 'bg-amber-100 text-amber-800 dark:bg-amber-400/15 dark:text-amber-300',
        'low' => 'bg-sky-100 text-sky-800 dark:bg-sky-400/15 dark:text-sky-300',
    ];
    $ocr = is_array($ocrCheck->ocr_result) ? $ocrCheck->ocr_result : [];
    $declared = $ocrCheck->declared_snapshot['batch'] ?? [];
    $fields = [
        'lot_number' => 'Numéro de lot',
        'production_date' => 'Date de production',
        'expiration_date' => 'Date d’expiration',
        'quantity' => 'Quantité',
    ];
    $mismatches = is_array($ocrCheck->mismatches) ? $ocrCheck->mismatches : [];
    $mismatchFields = collect($mismatches)->pluck('field')->all();
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
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-warm">Traceability workspace</p>
                <h1 class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">Résultat OCR / Étiquette</h1>
                <p class="mt-2 text-sm text-ink/55 dark:text-white/55">Analyse #<?php echo e($ocrCheck->id); ?> · <?php echo e($ocrCheck->created_at?->format('d/m/Y H:i')); ?></p>
            </div>
            <span class="w-fit rounded-full px-3 py-1.5 text-xs font-semibold <?php echo e($statusClasses[$ocrCheck->status] ?? 'bg-ink/10 text-ink/60 dark:bg-white/10 dark:text-white/60'); ?>"><?php echo e(ucfirst($ocrCheck->status)); ?></span>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-7xl space-y-5">
        <?php if($ocrCheck->status === 'pending'): ?>
            <div class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900 dark:border-amber-300/20 dark:bg-amber-400/10 dark:text-amber-200">
                <div class="flex h-10 w-10 shrink-0 animate-pulse items-center justify-center rounded-full bg-amber-warm/15">
                    <svg class="h-5 w-5 text-amber-warm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9"/></svg>
                </div>
                <div><p class="font-semibold">Analyse OCR en cours via Ollama</p><p class="mt-0.5 text-sm opacity-80">Actualisez cette page dans quelques instants pour consulter les résultats.</p></div>
            </div>
        <?php endif; ?>
        <?php if($ocrCheck->status === 'failed'): ?>
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-300/20 dark:bg-rose-400/10 dark:text-rose-300"><p class="font-semibold">L’analyse n’a pas abouti.</p><p class="mt-1"><?php echo e($ocrCheck->error_message ?: $ocrCheck->explanation); ?></p></div>
        <?php endif; ?>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="overflow-hidden rounded-2xl border border-ink/8 bg-white shadow-sm dark:border-white/10 dark:bg-[#1E3527]">
                <div class="flex h-[240px] items-center justify-center bg-cream p-4 sm:h-[300px] dark:bg-[#16281E]">
                    <img src="<?php echo e(asset('storage/' . $ocrCheck->image_path)); ?>" alt="Étiquette analysée" class="max-h-full max-w-full rounded-xl object-contain">
                </div>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-warm">Lot déclaré</p>
                            <h2 class="mt-1 truncate font-fraunces text-xl font-bold text-ink dark:text-white"><?php echo e($ocrCheck->product?->name ?? 'Produit indisponible'); ?></h2>
                            <p class="mt-1 text-sm text-ink/55 dark:text-white/55"><?php echo e($ocrCheck->batch?->lot_number ?? 'Lot indisponible'); ?></p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?php echo e($statusClasses[$ocrCheck->status] ?? ''); ?>"><?php echo e(ucfirst($ocrCheck->status)); ?></span>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-x-5 gap-y-3 border-t border-ink/8 pt-4 text-sm dark:border-white/10">
                        <div><dt class="text-xs text-ink/45 dark:text-white/45">Production</dt><dd class="mt-1 font-semibold text-ink dark:text-white"><?php echo e($declared['production_date'] ?? '—'); ?></dd></div>
                        <div><dt class="text-xs text-ink/45 dark:text-white/45">Expiration</dt><dd class="mt-1 font-semibold text-ink dark:text-white"><?php echo e($declared['expiration_date'] ?? '—'); ?></dd></div>
                        <div><dt class="text-xs text-ink/45 dark:text-white/45">Quantité déclarée</dt><dd class="mt-1 font-semibold text-ink dark:text-white"><?php echo e($declared['quantity'] ?? '—'); ?> <?php echo e($declared['unit'] ?? ''); ?></dd></div>
                        <div><dt class="text-xs text-ink/45 dark:text-white/45">Créée par</dt><dd class="mt-1 truncate font-semibold text-ink dark:text-white"><?php echo e($ocrCheck->creator?->name ?? 'Système'); ?></dd></div>
                    </dl>
                </div>
            </section>

            <section class="rounded-2xl border border-ink/8 bg-white p-5 shadow-sm sm:p-6 dark:border-white/10 dark:bg-[#1E3527]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-warm">Score d’incohérence</p>
                        <p class="mt-2 font-fraunces text-5xl font-bold <?php echo e($scoreTone['text']); ?>"><?php echo e($score !== null ? number_format($score, 1) : '—'); ?><span class="ml-1 text-lg font-semibold text-ink/40 dark:text-white/40">/100</span></p>
                        <p class="mt-1 text-sm font-semibold <?php echo e($scoreTone['text']); ?>"><?php echo e($scoreTone['label']); ?></p>
                    </div>
                    <span class="rounded-full px-3 py-1.5 text-xs font-semibold <?php echo e($statusClasses[$ocrCheck->status] ?? ''); ?>"><?php echo e(ucfirst($ocrCheck->status)); ?></span>
                </div>
                <progress class="mt-5 h-3 w-full overflow-hidden rounded-full <?php echo e($scoreTone['accent']); ?>" max="100" value="<?php echo e(min(max($score ?? 0, 0), 100)); ?>" aria-label="Score d’incohérence sur 100"></progress>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-cream p-4 dark:bg-[#16281E]">
                        <p class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Confiance OCR</p>
                        <p class="mt-2 font-fraunces text-3xl font-bold text-ink dark:text-white"><?php echo e($ocrCheck->confidence !== null ? number_format((float) $ocrCheck->confidence * 100, 1) . '%' : '—'); ?></p>
                    </div>
                    <div class="rounded-xl bg-cream p-4 dark:bg-[#16281E]">
                        <p class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Écarts détectés</p>
                        <p class="mt-2 font-fraunces text-3xl font-bold text-ink dark:text-white"><?php echo e(count($mismatches)); ?></p>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 border-t border-ink/8 pt-4 text-xs text-ink/50 dark:border-white/10 dark:text-white/50">
                    <span><span class="mr-1.5 inline-block h-2 w-2 rounded-full bg-emerald-500"></span>Faible · 0–20</span>
                    <span><span class="mr-1.5 inline-block h-2 w-2 rounded-full bg-amber-500"></span>Modéré · 21–50</span>
                    <span><span class="mr-1.5 inline-block h-2 w-2 rounded-full bg-rose-500"></span>Élevé · 51+</span>
                </div>
            </section>
        </div>

        <?php if($ocrCheck->status === 'completed'): ?>
            <section class="overflow-hidden rounded-2xl border border-ink/8 bg-white shadow-sm dark:border-white/10 dark:bg-[#1E3527]">
                <div class="border-b border-ink/8 px-5 py-4 sm:px-6 dark:border-white/10">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-warm">Comparaison du lot</p>
                    <h2 class="mt-1 font-fraunces text-xl font-bold text-ink dark:text-white">Données extraites et déclarées</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink/8 dark:divide-white/10">
                        <thead class="bg-cream/70 dark:bg-[#16281E]"><tr>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45 sm:px-6">Champ</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45 sm:px-6">Lecture OCR</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45 sm:px-6">Donnée déclarée</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45 sm:px-6">Résultat</th>
                        </tr></thead>
                        <tbody class="divide-y divide-ink/8 dark:divide-white/10">
                            <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $different = in_array($field, $mismatchFields, true); ?>
                                <tr>
                                    <th scope="row" class="whitespace-nowrap px-5 py-3.5 text-left text-sm font-semibold text-ink dark:text-white sm:px-6"><?php echo e($label); ?></th>
                                    <td class="px-5 py-3.5 text-sm <?php echo e($different ? 'font-semibold text-rose-700 dark:text-rose-300' : 'text-ink/70 dark:text-white/70'); ?>"><?php echo e(filled($ocr[$field] ?? null) ? $ocr[$field] : 'Non lu'); ?><?php if($field === 'quantity' && !empty($ocr['unit'])): ?> <?php echo e($ocr['unit']); ?><?php endif; ?></td>
                                    <td class="px-5 py-3.5 text-sm text-ink/70 dark:text-white/70"><?php echo e(filled($declared[$field] ?? null) ? $declared[$field] : '—'); ?><?php if($field === 'quantity' && !empty($declared['unit'])): ?> <?php echo e($declared['unit']); ?><?php endif; ?></td>
                                    <td class="px-5 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($different ? 'bg-rose-100 text-rose-800 dark:bg-rose-400/15 dark:text-rose-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300'); ?>"><?php echo e($different ? 'Écart' : 'Conforme'); ?></span></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border <?php echo e($mismatches !== [] ? 'border-rose-200/70 bg-rose-50/70 dark:border-rose-300/15 dark:bg-rose-400/10' : 'border-emerald-200/70 bg-emerald-50/70 dark:border-emerald-300/15 dark:bg-emerald-400/10'); ?> p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] <?php echo e($mismatches !== [] ? 'text-rose-800/70 dark:text-rose-200/70' : 'text-emerald-800/70 dark:text-emerald-200/70'); ?>">Incohérences détectées</p>
                        <h2 class="mt-1 font-fraunces text-xl font-bold <?php echo e($mismatches !== [] ? 'text-rose-950 dark:text-rose-100' : 'text-emerald-950 dark:text-emerald-100'); ?>"><?php echo e($mismatches !== [] ? count($mismatches) . ' écart(s) à vérifier' : 'Aucun écart détecté'); ?></h2>
                    </div>
                    <?php if($mismatches !== []): ?>
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800 dark:bg-rose-400/15 dark:text-rose-300"><?php echo e(number_format($score ?? 0, 1)); ?> / 100</span>
                    <?php endif; ?>
                </div>
                <?php if($mismatches !== []): ?>
                    <ul class="mt-4 divide-y divide-rose-900/10 dark:divide-rose-100/10">
                        <?php $__currentLoopData = $mismatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mismatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex flex-col gap-2 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="font-semibold text-rose-950 dark:text-rose-100"><?php echo e($fields[$mismatch['field']] ?? str_replace('_', ' ', ucfirst($mismatch['field']))); ?></p>
                                    <p class="mt-1 break-words text-sm text-rose-900/80 dark:text-rose-100/80">OCR: <strong><?php echo e($mismatch['ocr_value']); ?></strong><span class="mx-2">·</span>Déclaré: <strong><?php echo e($mismatch['declared_value']); ?></strong></p>
                                </div>
                                <span class="w-fit shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase <?php echo e($severityClasses[$mismatch['severity']] ?? 'bg-ink/10 text-ink/60'); ?>"><?php echo e($mismatch['severity']); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section class="rounded-2xl border border-ink/8 bg-white p-5 shadow-sm sm:p-6 dark:border-white/10 dark:bg-[#1E3527]">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-warm">Synthèse</p>
                <p class="mt-2 text-[15px] leading-7 text-ink/75 dark:text-white/75"><?php echo e($ocrCheck->explanation); ?></p>
                <dl class="mt-5 grid gap-x-6 gap-y-4 border-t border-ink/8 pt-5 sm:grid-cols-2 dark:border-white/10">
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Nom du produit lu</dt><dd class="mt-1 text-sm font-semibold text-ink dark:text-white"><?php echo e($ocr['product_name'] ?: 'Non lu'); ?></dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Quantité et unité OCR</dt><dd class="mt-1 text-sm font-semibold text-ink dark:text-white"><?php echo e($ocr['quantity'] ?? '—'); ?> <?php echo e($ocr['unit'] ?? ''); ?></dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Allergènes lus</dt><dd class="mt-1 text-sm text-ink dark:text-white"><?php echo e(count($ocr['allergens'] ?? []) ? implode(', ', $ocr['allergens']) : 'Aucun renseigné'); ?></dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Certifications lues</dt><dd class="mt-1 text-sm text-ink dark:text-white"><?php echo e(count($ocr['certifications'] ?? []) ? implode(', ', $ocr['certifications']) : 'Aucune renseignée'); ?></dd></div>
                </dl>
                <?php if(!empty($ocr['other_text'])): ?>
                    <div class="mt-4 border-t border-ink/8 pt-4 dark:border-white/10"><p class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Autre texte détecté</p><p class="mt-1 whitespace-pre-line text-sm text-ink dark:text-white"><?php echo e($ocr['other_text']); ?></p></div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <div class="flex flex-wrap justify-end gap-3 border-t border-ink/8 pt-4 dark:border-white/10">
            <a href="<?php echo e(route('admin.ocr-checks.index')); ?>" class="inline-flex items-center rounded-xl border border-ink/10 px-4 py-2.5 text-sm font-semibold text-ink/70 hover:bg-cream dark:border-white/10 dark:text-white/70 dark:hover:bg-white/5">Retour aux analyses</a>
            <form action="<?php echo e(route('admin.ocr-checks.destroy', $ocrCheck)); ?>" method="POST" onsubmit="return confirm('Supprimer cette analyse ?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50 dark:border-rose-300/20 dark:text-rose-300 dark:hover:bg-rose-400/10">Supprimer</button></form>
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
<?php endif; ?><?php /**PATH C:\Users\mdain\nutritrace\resources\views/admin/ocr-checks/show.blade.php ENDPATH**/ ?>