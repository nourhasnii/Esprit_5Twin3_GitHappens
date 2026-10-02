@php
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
@endphp
<x-dashboard-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-warm">Traceability workspace</p>
                <h1 class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">Résultat OCR / Étiquette</h1>
                <p class="mt-2 text-sm text-ink/55 dark:text-white/55">Analyse #{{ $ocrCheck->id }} · {{ $ocrCheck->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <span class="w-fit rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClasses[$ocrCheck->status] ?? 'bg-ink/10 text-ink/60 dark:bg-white/10 dark:text-white/60' }}">{{ ucfirst($ocrCheck->status) }}</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5">
        @if($ocrCheck->status === 'pending')
            <div class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900 dark:border-amber-300/20 dark:bg-amber-400/10 dark:text-amber-200">
                <div class="flex h-10 w-10 shrink-0 animate-pulse items-center justify-center rounded-full bg-amber-warm/15">
                    <svg class="h-5 w-5 text-amber-warm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9"/></svg>
                </div>
                <div><p class="font-semibold">Analyse OCR en cours via Ollama</p><p class="mt-0.5 text-sm opacity-80">Actualisez cette page dans quelques instants pour consulter les résultats.</p></div>
            </div>
        @endif
        @if($ocrCheck->status === 'failed')
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-300/20 dark:bg-rose-400/10 dark:text-rose-300"><p class="font-semibold">L’analyse n’a pas abouti.</p><p class="mt-1">{{ $ocrCheck->error_message ?: $ocrCheck->explanation }}</p></div>
        @endif

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="overflow-hidden rounded-2xl border border-ink/8 bg-white shadow-sm dark:border-white/10 dark:bg-[#1E3527]">
                <div class="flex h-[240px] items-center justify-center bg-cream p-4 sm:h-[300px] dark:bg-[#16281E]">
                    <img src="{{ asset('storage/' . $ocrCheck->image_path) }}" alt="Étiquette analysée" class="max-h-full max-w-full rounded-xl object-contain">
                </div>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-warm">Lot déclaré</p>
                            <h2 class="mt-1 truncate font-fraunces text-xl font-bold text-ink dark:text-white">{{ $ocrCheck->product?->name ?? 'Produit indisponible' }}</h2>
                            <p class="mt-1 text-sm text-ink/55 dark:text-white/55">{{ $ocrCheck->batch?->lot_number ?? 'Lot indisponible' }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$ocrCheck->status] ?? '' }}">{{ ucfirst($ocrCheck->status) }}</span>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-x-5 gap-y-3 border-t border-ink/8 pt-4 text-sm dark:border-white/10">
                        <div><dt class="text-xs text-ink/45 dark:text-white/45">Production</dt><dd class="mt-1 font-semibold text-ink dark:text-white">{{ $declared['production_date'] ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-ink/45 dark:text-white/45">Expiration</dt><dd class="mt-1 font-semibold text-ink dark:text-white">{{ $declared['expiration_date'] ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-ink/45 dark:text-white/45">Quantité déclarée</dt><dd class="mt-1 font-semibold text-ink dark:text-white">{{ $declared['quantity'] ?? '—' }} {{ $declared['unit'] ?? '' }}</dd></div>
                        <div><dt class="text-xs text-ink/45 dark:text-white/45">Créée par</dt><dd class="mt-1 truncate font-semibold text-ink dark:text-white">{{ $ocrCheck->creator?->name ?? 'Système' }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="rounded-2xl border border-ink/8 bg-white p-5 shadow-sm sm:p-6 dark:border-white/10 dark:bg-[#1E3527]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-warm">Score d’incohérence</p>
                        <p class="mt-2 font-fraunces text-5xl font-bold {{ $scoreTone['text'] }}">{{ $score !== null ? number_format($score, 1) : '—' }}<span class="ml-1 text-lg font-semibold text-ink/40 dark:text-white/40">/100</span></p>
                        <p class="mt-1 text-sm font-semibold {{ $scoreTone['text'] }}">{{ $scoreTone['label'] }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClasses[$ocrCheck->status] ?? '' }}">{{ ucfirst($ocrCheck->status) }}</span>
                </div>
                <progress class="mt-5 h-3 w-full overflow-hidden rounded-full {{ $scoreTone['accent'] }}" max="100" value="{{ min(max($score ?? 0, 0), 100) }}" aria-label="Score d’incohérence sur 100"></progress>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-cream p-4 dark:bg-[#16281E]">
                        <p class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Confiance OCR</p>
                        <p class="mt-2 font-fraunces text-3xl font-bold text-ink dark:text-white">{{ $ocrCheck->confidence !== null ? number_format((float) $ocrCheck->confidence * 100, 1) . '%' : '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-cream p-4 dark:bg-[#16281E]">
                        <p class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Écarts détectés</p>
                        <p class="mt-2 font-fraunces text-3xl font-bold text-ink dark:text-white">{{ count($mismatches) }}</p>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 border-t border-ink/8 pt-4 text-xs text-ink/50 dark:border-white/10 dark:text-white/50">
                    <span><span class="mr-1.5 inline-block h-2 w-2 rounded-full bg-emerald-500"></span>Faible · 0–20</span>
                    <span><span class="mr-1.5 inline-block h-2 w-2 rounded-full bg-amber-500"></span>Modéré · 21–50</span>
                    <span><span class="mr-1.5 inline-block h-2 w-2 rounded-full bg-rose-500"></span>Élevé · 51+</span>
                </div>
            </section>
        </div>

        @if($ocrCheck->status === 'completed')
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
                            @foreach($fields as $field => $label)
                                @php $different = in_array($field, $mismatchFields, true); @endphp
                                <tr>
                                    <th scope="row" class="whitespace-nowrap px-5 py-3.5 text-left text-sm font-semibold text-ink dark:text-white sm:px-6">{{ $label }}</th>
                                    <td class="px-5 py-3.5 text-sm {{ $different ? 'font-semibold text-rose-700 dark:text-rose-300' : 'text-ink/70 dark:text-white/70' }}">{{ filled($ocr[$field] ?? null) ? $ocr[$field] : 'Non lu' }}@if($field === 'quantity' && !empty($ocr['unit'])) {{ $ocr['unit'] }}@endif</td>
                                    <td class="px-5 py-3.5 text-sm text-ink/70 dark:text-white/70">{{ filled($declared[$field] ?? null) ? $declared[$field] : '—' }}@if($field === 'quantity' && !empty($declared['unit'])) {{ $declared['unit'] }}@endif</td>
                                    <td class="px-5 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $different ? 'bg-rose-100 text-rose-800 dark:bg-rose-400/15 dark:text-rose-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300' }}">{{ $different ? 'Écart' : 'Conforme' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border {{ $mismatches !== [] ? 'border-rose-200/70 bg-rose-50/70 dark:border-rose-300/15 dark:bg-rose-400/10' : 'border-emerald-200/70 bg-emerald-50/70 dark:border-emerald-300/15 dark:bg-emerald-400/10' }} p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] {{ $mismatches !== [] ? 'text-rose-800/70 dark:text-rose-200/70' : 'text-emerald-800/70 dark:text-emerald-200/70' }}">Incohérences détectées</p>
                        <h2 class="mt-1 font-fraunces text-xl font-bold {{ $mismatches !== [] ? 'text-rose-950 dark:text-rose-100' : 'text-emerald-950 dark:text-emerald-100' }}">{{ $mismatches !== [] ? count($mismatches) . ' écart(s) à vérifier' : 'Aucun écart détecté' }}</h2>
                    </div>
                    @if($mismatches !== [])
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800 dark:bg-rose-400/15 dark:text-rose-300">{{ number_format($score ?? 0, 1) }} / 100</span>
                    @endif
                </div>
                @if($mismatches !== [])
                    <ul class="mt-4 divide-y divide-rose-900/10 dark:divide-rose-100/10">
                        @foreach($mismatches as $mismatch)
                            <li class="flex flex-col gap-2 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="font-semibold text-rose-950 dark:text-rose-100">{{ $fields[$mismatch['field']] ?? str_replace('_', ' ', ucfirst($mismatch['field'])) }}</p>
                                    <p class="mt-1 break-words text-sm text-rose-900/80 dark:text-rose-100/80">OCR: <strong>{{ $mismatch['ocr_value'] }}</strong><span class="mx-2">·</span>Déclaré: <strong>{{ $mismatch['declared_value'] }}</strong></p>
                                </div>
                                <span class="w-fit shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase {{ $severityClasses[$mismatch['severity']] ?? 'bg-ink/10 text-ink/60' }}">{{ $mismatch['severity'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="rounded-2xl border border-ink/8 bg-white p-5 shadow-sm sm:p-6 dark:border-white/10 dark:bg-[#1E3527]">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-warm">Synthèse</p>
                <p class="mt-2 text-[15px] leading-7 text-ink/75 dark:text-white/75">{{ $ocrCheck->explanation }}</p>
                <dl class="mt-5 grid gap-x-6 gap-y-4 border-t border-ink/8 pt-5 sm:grid-cols-2 dark:border-white/10">
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Nom du produit lu</dt><dd class="mt-1 text-sm font-semibold text-ink dark:text-white">{{ $ocr['product_name'] ?: 'Non lu' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Quantité et unité OCR</dt><dd class="mt-1 text-sm font-semibold text-ink dark:text-white">{{ $ocr['quantity'] ?? '—' }} {{ $ocr['unit'] ?? '' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Allergènes lus</dt><dd class="mt-1 text-sm text-ink dark:text-white">{{ count($ocr['allergens'] ?? []) ? implode(', ', $ocr['allergens']) : 'Aucun renseigné' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Certifications lues</dt><dd class="mt-1 text-sm text-ink dark:text-white">{{ count($ocr['certifications'] ?? []) ? implode(', ', $ocr['certifications']) : 'Aucune renseignée' }}</dd></div>
                </dl>
                @if(!empty($ocr['other_text']))
                    <div class="mt-4 border-t border-ink/8 pt-4 dark:border-white/10"><p class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Autre texte détecté</p><p class="mt-1 whitespace-pre-line text-sm text-ink dark:text-white">{{ $ocr['other_text'] }}</p></div>
                @endif
            </section>
        @endif

        <div class="flex flex-wrap justify-end gap-3 border-t border-ink/8 pt-4 dark:border-white/10">
            <a href="{{ route('admin.ocr-checks.index') }}" class="inline-flex items-center rounded-xl border border-ink/10 px-4 py-2.5 text-sm font-semibold text-ink/70 hover:bg-cream dark:border-white/10 dark:text-white/70 dark:hover:bg-white/5">Retour aux analyses</a>
            <form action="{{ route('admin.ocr-checks.destroy', $ocrCheck) }}" method="POST" onsubmit="return confirm('Supprimer cette analyse ?')">@csrf @method('DELETE')<button class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50 dark:border-rose-300/20 dark:text-rose-300 dark:hover:bg-rose-400/10">Supprimer</button></form>
        </div>
    </div>
</x-dashboard-layout>