<x-dashboard-layout>
    @php
        $entityLabel = match ($type) {
            'product' => $entity->name,
            'batch' => $entity->lot_number,
            'supplier' => $entity->organization_name ?: $entity->name,
            default => '#'.$entity->getKey(),
        };
        $entityTypeLabel = match ($type) {
            'product' => 'Produit',
            'batch' => 'Lot',
            'supplier' => 'Fournisseur',
            default => ucfirst($type),
        };
        $severityBadge = fn (string $severity): string => match ($severity) {
            'critical' => 'bg-red-500/12 text-red-700 border border-red-500/15 dark:text-red-300',
            'warning' => 'bg-amber-warm/12 text-amber-warm border border-amber-warm/20',
            default => 'bg-emerald-500/12 text-emerald-700 border border-emerald-500/15 dark:text-emerald-300',
        };
        $severityLabel = fn (string $severity): string => match ($severity) {
            'critical' => 'Critique',
            'warning' => 'Avertissement',
            default => 'Info',
        };
        $riskFindings = $evaluation['risk_findings'];
        $maxContribution = max(1, (float) (collect($riskFindings)->max('contribution') ?: 1));

        // --- Mise en page du graphe d'impact (SVG construit à la main) ---
        $graphNodes = collect($impact['nodes']);
        $byDepth = $graphNodes->groupBy('depth')->sortKeys();
        $maxDepth = (int) $graphNodes->max('depth');
        $columnWidth = 226;
        $nodeRadius = 26;
        $rowGap = 78;
        $paddingX = 44;
        $paddingY = 34;
        $maxRows = max(1, $byDepth->map->count()->max());
        $graphHeight = max(230, $maxRows * $rowGap + 2 * $paddingY);
        $graphWidth = $paddingX * 2 + $nodeRadius * 2 + $maxDepth * $columnWidth;

        $positions = [];
        foreach ($byDepth as $depth => $group) {
            $count = $group->count();
            $startY = ($graphHeight - ($count - 1) * $rowGap) / 2;
            foreach ($group->values() as $i => $node) {
                $positions[$node['key']] = [
                    'x' => $paddingX + $nodeRadius + $depth * $columnWidth,
                    'y' => $startY + $i * $rowGap,
                ];
            }
        }

        $impactColor = fn (string $impact): string => match ($impact) {
            'confirmed' => '#b23a3a',
            'potential' => '#c17817',
            default => '#9aa5a0',
        };
        $impactLabel = fn (string $impact): string => match ($impact) {
            'confirmed' => 'Confirmé',
            'potential' => 'Potentiel',
            default => 'Incertain',
        };
        $conclusionMeta = [
            'improvement_confirmed' => ['label' => 'Amélioration confirmée', 'class' => 'border-emerald-500/25 bg-emerald-500/10 text-emerald-800 dark:text-emerald-300'],
            'improvement_unconfirmed' => ['label' => 'Baisse non confirmée', 'class' => 'border-amber-warm/25 bg-amber-warm/10 text-amber-warm'],
            'regression' => ['label' => 'Régression du risque', 'class' => 'border-red-500/25 bg-red-500/10 text-red-700 dark:text-red-300'],
            'stable' => ['label' => 'Situation stable', 'class' => 'border-ink/10 bg-ink/5 text-ink/70 dark:border-white/10 dark:text-white/70'],
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">{{ __('NutriMind · :entitytypelabel', ['entitytypelabel' => $entityTypeLabel]) }}</p>
                <h1 class="mt-1 font-fraunces text-3xl font-bold leading-tight text-ink dark:text-white">{{ $entityLabel }}</h1>
                <p class="mt-2 text-sm text-ink/55 dark:text-white/55">{{ __('Évaluation explicable, impact, simulation et recommandations correctives.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.nutrimind.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-ink/10 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition-colors hover:bg-cream dark:border-white/10 dark:bg-[#1b2923] dark:text-white">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19 5 12l7-7"/></svg>
                    {{ __('Vue d’ensemble') }}
                </a>
                <form method="POST" action="{{ route('admin.nutrimind.assess', ['type' => $type, 'id' => $entity->getKey()]) }}"
                      x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <button type="submit" :disabled="submitting"
                            class="inline-flex items-center gap-2 rounded-lg bg-forest px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-forest-dark disabled:cursor-not-allowed disabled:opacity-60">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        <span x-show="!submitting">{{ __('Enregistrer l’évaluation') }}</span>
                        <span x-show="submitting" x-cloak>{{ __('Enregistrement…') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[1440px] space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</div>
        @endif

        {{-- Risque + complétude + impact --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="flex flex-col items-center justify-center rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923]">
                <x-dashboard.risk-gauge :score="$evaluation['score']" :severity="$evaluation['severity']" :caption="__('Score de risque')" />
                @if($evaluation['critical_override'])
                    <p class="mt-3 rounded-lg bg-red-500/10 px-3 py-1.5 text-center text-[11px] font-semibold text-red-700 dark:text-red-300">
                        {{ __('Incident de sécurité confirmé — sévérité critique forcée.') }}
                    </p>
                @endif
            </div>

            <div class="rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-[11px] font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">{{ __('Complétude des données') }}</p>
                <p class="mt-2 font-fraunces text-3xl font-bold text-ink dark:text-white">{{ number_format($evaluation['data_completeness'], 0) }}<span class="text-lg text-ink/40">%</span></p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-ink/5 dark:bg-white/10">
                    <div class="h-full bg-forest transition-all" style="width: {{ max(0, min(100, (float) $evaluation['data_completeness'])) }}%"></div>
                </div>
                <p class="mt-3 text-xs text-ink/55 dark:text-white/55">{{ __(':missing_findings information(s) manquante(s), hors score.', ['missing_findings' => count($evaluation['missing_findings'])]) }}</p>
            </div>

            <div class="rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-[11px] font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">{{ __('Constatations') }}</p>
                <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                    <div>
                        <p class="font-fraunces text-2xl font-bold text-red-600 dark:text-red-300">{{ $evaluation['counts']['critical'] ?? 0 }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink/40 dark:text-white/40">{{ __('Critiques') }}</p>
                    </div>
                    <div>
                        <p class="font-fraunces text-2xl font-bold text-amber-warm">{{ $evaluation['counts']['warning'] ?? 0 }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink/40 dark:text-white/40">{{ __('Alerte') }}</p>
                    </div>
                    <div>
                        <p class="font-fraunces text-2xl font-bold text-emerald-600 dark:text-emerald-300">{{ $evaluation['counts']['info'] ?? 0 }}</p>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-ink/40 dark:text-white/40">{{ __('Info') }}</p>
                    </div>
                </div>
                <p class="mt-3 text-xs text-ink/55 dark:text-white/55">{{ __('Version du jeu de règles : :rule_set_version.', ['rule_set_version' => $evaluation['rule_set_version']]) }}</p>
            </div>

            <div class="rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923]">
                <p class="text-[11px] font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">{{ __('Impact (graphe)') }}</p>
                <div class="mt-3 space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-ink/60 dark:text-white/60">{{ __('Confirmé') }}</span>
                        <span class="font-bold text-red-600 dark:text-red-300">{{ $impact['summary']['confirmed'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-ink/60 dark:text-white/60">{{ __('Potentiel') }}</span>
                        <span class="font-bold text-amber-warm">{{ $impact['summary']['potential'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-ink/60 dark:text-white/60">{{ __('Incertain') }}</span>
                        <span class="font-bold text-ink/70 dark:text-white/70">{{ $impact['summary']['unknown'] ?? 0 }}</span>
                    </div>
                </div>
                <p class="mt-3 text-xs text-ink/55 dark:text-white/55">{{ __(':nodes entité(s) explorée(s), profondeur ≤ :max_depth.', ['nodes' => count($impact['nodes']), 'max_depth' => $impact['bounds']['max_depth']]) }}</p>
            </div>
        </section>

        {{-- Facteurs contributifs + données manquantes --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923] lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-fraunces text-lg font-bold text-ink dark:text-white">{{ __('Facteurs contributifs') }}</h3>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink/40 dark:text-white/40">{{ __('chaque point est justifié') }}</span>
                </div>
                <ul class="space-y-3">
                    @forelse($riskFindings as $finding)
                        <li class="rounded-xl border border-ink/5 bg-cream/40 p-4 dark:border-white/10 dark:bg-white/5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $severityBadge($finding['severity']) }}">{{ $severityLabel($finding['severity']) }}</span>
                                    <span class="font-semibold text-ink dark:text-white">{{ $finding['label'] }}</span>
                                </div>
                                <span class="font-fraunces text-lg font-bold text-ink dark:text-white">+{{ rtrim(rtrim(number_format((float) $finding['contribution'], 2, '.', ''), '0'), '.') }}</span>
                            </div>
                            <p class="mt-2 text-sm leading-relaxed text-ink/75 dark:text-white/75">{{ $finding['reason'] }}</p>
                            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-ink/5 dark:bg-white/10">
                                    <div class="h-full bg-red-500/70" style="width: {{ round((float) $finding['contribution'] / $maxContribution * 100) }}%"></div>
                                </div>
                                <span class="text-[11px] text-ink/50 dark:text-white/50">{{ __('Source : :source_label', ['source_label' => $finding['source_label'] ?? '—']) }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-emerald-500/20 bg-emerald-500/5 p-6 text-center text-sm italic text-emerald-700 dark:text-emerald-300">
                            {{ __('Aucun risque confirmé — le score est de 0.') }}
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923]">
                <h3 class="mb-4 font-fraunces text-lg font-bold text-ink dark:text-white">{{ __('Informations manquantes') }}</h3>
                <ul class="space-y-2.5">
                    @forelse($evaluation['missing_findings'] as $finding)
                        <li class="flex items-start gap-3 rounded-xl bg-ink/5 p-3 dark:bg-white/5">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-warm/15 text-amber-warm">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 8v5M12 16h.01"/></svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-ink dark:text-white">{{ $finding['label'] }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-ink/60 dark:text-white/60">{{ $finding['reason'] }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-ink/10 p-6 text-center text-sm italic text-ink/40 dark:border-white/10 dark:text-white/40">
                            {{ __('Toutes les données attendues sont présentes.') }}
                        </li>
                    @endforelse
                </ul>
            </div>
        </section>

        {{-- Recommandations sûres --}}
        <section class="rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923]">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="font-fraunces text-lg font-bold text-ink dark:text-white">{{ __('Recommandations correctives') }}</h3>
                    <p class="text-xs text-ink/50 dark:text-white/50">{{ __('Les actions réutilisent les workflows existants ; aucune action n’est déclenchée automatiquement.') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @forelse($recommendations as $recommendation)
                    <div class="flex flex-col rounded-xl border border-ink/5 bg-cream/40 p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $severityBadge($recommendation['severity']) }}">{{ $severityLabel($recommendation['severity']) }}</span>
                            @if($recommendation['consequential'])
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-500/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-700 dark:text-red-300">
                                    {{ __('Action sensible') }}
                                </span>
                            @endif
                        </div>
                        <p class="mt-2 font-semibold text-ink dark:text-white">{{ $recommendation['title'] }}</p>
                        @if($recommendation['guidance'])
                            <p class="mt-1 text-sm leading-relaxed text-ink/70 dark:text-white/70">{{ $recommendation['guidance'] }}</p>
                        @endif
                        <p class="mt-2 text-xs text-ink/50 dark:text-white/50">{{ __('Constatation : :reason', ['reason' => $recommendation['reason']]) }}</p>

                        <div class="mt-3 flex flex-wrap items-center gap-2 pt-3 border-t border-ink/5 dark:border-white/10">
                            @if($recommendation['action'])
                                <a href="{{ $recommendation['action']['url'] }}"
                                   @if($recommendation['consequential']) onclick="return confirm('Action sensible : {{ $recommendation['title'] }}. Confirmer l’ouverture de ce workflow ?')" @endif
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-forest/10 px-3 py-1.5 text-xs font-semibold text-forest transition-colors hover:bg-forest/20 dark:text-emerald-300">
                                    {{ $recommendation['action']['label'] }}
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </a>
                            @else
                                <span class="text-[11px] italic text-ink/45 dark:text-white/45">{{ __('Aucun workflow dédié — suivez la recommandation ci-dessus.') }}</span>
                            @endif

                            <form method="POST" action="{{ route('admin.nutrimind.actions.store', ['type' => $type, 'id' => $entity->getKey()]) }}" class="inline">
                                @csrf
                                <input type="hidden" name="rule" value="{{ $recommendation['rule'] }}">
                                <input type="hidden" name="label" value="{{ $recommendation['title'] }}">
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-ink/10 px-3 py-1.5 text-xs font-semibold text-ink/70 transition-colors hover:bg-white dark:border-white/10 dark:text-white/70">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                                    {{ __('Journaliser') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-2 rounded-xl border border-dashed border-emerald-500/20 bg-emerald-500/5 p-6 text-center text-sm italic text-emerald-700 dark:text-emerald-300">
                        {{ __('Aucune recommandation : aucune constatation de risque ni donnée manquante.') }}
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Graphe d'impact --}}
        <section class="rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923]">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-fraunces text-lg font-bold text-ink dark:text-white">{{ __('Graphe d’impact') }}</h3>
                    <p class="text-xs text-ink/50 dark:text-white/50">{{ __('Dépendances directes et indirectes issues des relations réelles. Un nœud n’est visité qu’une fois.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-[11px] font-semibold">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background:#b23a3a"></span>{{ __('Confirmé') }}</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background:#c17817"></span>{{ __('Potentiel') }}</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background:#9aa5a0"></span>{{ __('Incertain') }}</span>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl bg-cream/40 p-2 dark:bg-white/5">
                <svg viewBox="0 0 {{ $graphWidth }} {{ $graphHeight }}" width="{{ max(640, $graphWidth) }}" height="{{ $graphHeight }}" role="img" aria-label="{{ __('Graphe d’impact des entités affectées') }}" class="max-w-none">
                    @foreach($impact['edges'] as $edge)
                        @php($from = $positions[$edge['from']] ?? null)
                        @php($to = $positions[$edge['to']] ?? null)
                        @if($from && $to)
                            <line x1="{{ $from['x'] }}" y1="{{ $from['y'] }}" x2="{{ $to['x'] }}" y2="{{ $to['y'] }}"
                                  stroke="{{ $edge['cycle'] ? '#d9c3a0' : '#c8d3cc' }}"
                                  stroke-width="{{ $edge['cycle'] ? 1.2 : 1.6 }}"
                                  @if($edge['cycle']) stroke-dasharray="4 4" @endif />
                        @endif
                    @endforeach

                    @foreach($impact['nodes'] as $node)
                        @php($pos = $positions[$node['key']] ?? null)
                        @if($pos)
                            <circle cx="{{ $pos['x'] }}" cy="{{ $pos['y'] }}" r="{{ $nodeRadius }}"
                                    fill="{{ $impactColor($node['impact']) }}" fill-opacity="0.16"
                                    stroke="{{ $impactColor($node['impact']) }}" stroke-width="2" />
                            <text x="{{ $pos['x'] }}" y="{{ $pos['y'] - 2 }}" text-anchor="middle" font-size="10" font-weight="700" fill="{{ $impactColor($node['impact']) }}">
                                {{ \Illuminate\Support\Str::upper(mb_substr($node['kind'], 0, 10)) }}
                            </text>
                            <text x="{{ $pos['x'] }}" y="{{ $pos['y'] + 10 }}" text-anchor="middle" font-size="9" fill="#5b6a62">
                                #{{ $node['id'] }} · p{{ $node['depth'] }}
                            </text>
                            <text x="{{ $pos['x'] }}" y="{{ $pos['y'] + $nodeRadius + 13 }}" text-anchor="middle" font-size="10" font-weight="600" fill="#1c1c1a">
                                {{ \Illuminate\Support\Str::limit($node['label'], 20) }}
                            </text>
                            @if(!empty($node['signal']))
                                <text x="{{ $pos['x'] }}" y="{{ $pos['y'] + $nodeRadius + 25 }}" text-anchor="middle" font-size="9" font-style="italic" fill="{{ $impactColor($node['impact']) }}">
                                    {{ $node['signal']['label'] }}
                                </text>
                            @endif
                        @endif
                    @endforeach
                </svg>
            </div>

            @if($impact['bounds']['truncated'])
                <p class="mt-3 rounded-lg bg-amber-warm/10 px-3 py-2 text-xs font-medium text-amber-warm">
                    {{ __('Exploration tronquée : le nombre de nœuds a atteint la limite (:max_nodes).', ['max_nodes' => $impact['bounds']['max_nodes']]) }}
                </p>
            @endif

            <details class="mt-4">
                <summary class="cursor-pointer text-sm font-semibold text-ink/70 dark:text-white/70">{{ __('Détail des chemins de dépendance') }}</summary>
                <ul class="mt-3 space-y-2">
                    @foreach($impact['nodes'] as $node)
                        @continue(empty($node['path']))
                        <li class="rounded-lg border border-ink/5 bg-cream/40 px-3 py-2 text-xs dark:border-white/10 dark:bg-white/5">
                            <span class="font-semibold text-ink dark:text-white">{{ $node['label'] }}</span>
                            <span class="text-ink/50 dark:text-white/50">
                                ← {{ collect($node['path'])->map(fn ($step) => $step['from'].' → '.$step['to'].' ('.$step['relation'].')')->implode(' ; ') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </details>
        </section>

        {{-- Simulation what-if --}}
        <section class="rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923]">
            <div class="mb-4">
                <h3 class="font-fraunces text-lg font-bold text-ink dark:text-white">{{ __('Simulation « what-if »') }}</h3>
                <p class="text-xs text-ink/50 dark:text-white/50">{{ __('Compare l’état réel à un scénario hypothétique. Aucune donnée réelle n’est modifiée.') }}</p>
            </div>

            <form method="GET" action="{{ route('admin.nutrimind.show', ['type' => $type, 'id' => $entity->getKey()]) }}" class="mb-5">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($scenarios as $scenario)
                        <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-ink/10 bg-cream/40 p-3 text-sm transition-colors hover:border-forest/30 dark:border-white/10 dark:bg-white/5">
                            <input type="checkbox" name="scenarios[]" value="{{ $scenario['key'] }}" @checked(in_array($scenario['key'], $selected, true))
                                   class="mt-0.5 h-4 w-4 rounded border-ink/20 text-forest focus:ring-forest">
                            <span>
                                <span class="block font-semibold text-ink dark:text-white">{{ $scenario['label'] }}</span>
                                <span class="mt-0.5 block text-xs text-ink/55 dark:text-white/55">{{ $scenario['description'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-forest px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-forest-dark">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 3v4M3 5h4M6 17v4M4 19h4M13 3l2.5 6.5L22 12l-6.5 2.5L13 21l-2.5-6.5L4 12l6.5-2.5L13 3Z"/></svg>
                        {{ __('Lancer la simulation') }}
                    </button>
                    @if($simulation)
                        <a href="{{ route('admin.nutrimind.show', ['type' => $type, 'id' => $entity->getKey()]) }}" class="text-sm font-semibold text-ink/60 hover:text-ink dark:text-white/60">{{ __('Réinitialiser') }}</a>
                    @endif
                </div>
            </form>

            @if($simulation)
                <div class="rounded-xl border border-amber-warm/25 bg-amber-warm/5 p-4">
                    <p class="mb-4 text-[11px] font-bold uppercase tracking-wider text-amber-warm">
                        {{ __('Résultat hypothétique — scénario(s) : :label', ['label' => collect($simulation['scenarios'])->pluck('label')->implode(', ')]) }}
                    </p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-xl border border-ink/5 bg-white p-4 text-center dark:border-white/10 dark:bg-[#1b2923]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">{{ __('Réel') }}</p>
                            <p class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">{{ number_format($simulation['baseline']['score'], 0) }}</p>
                            <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $severityBadge($simulation['baseline']['severity']) }}">{{ $severityLabel($simulation['baseline']['severity']) }}</span>
                        </div>
                        <div class="flex flex-col items-center justify-center rounded-xl border border-ink/5 bg-white p-4 text-center dark:border-white/10 dark:bg-[#1b2923]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">{{ __('Écart') }}</p>
                            <p class="mt-1 font-fraunces text-3xl font-bold {{ $simulation['delta']['score'] > 0 ? 'text-red-600 dark:text-red-300' : 'text-emerald-600 dark:text-emerald-300' }}">
                                {{ $simulation['delta']['score'] > 0 ? '+' : '' }}{{ number_format($simulation['delta']['score'], 0) }}
                            </p>
                            @if($simulation['delta']['severity_changed'])
                                <p class="mt-1 text-xs text-ink/55 dark:text-white/55">{{ $severityLabel($simulation['delta']['from']) }} → {{ $severityLabel($simulation['delta']['to']) }}</p>
                            @endif
                        </div>
                        <div class="rounded-xl border border-ink/5 bg-white p-4 text-center dark:border-white/10 dark:bg-[#1b2923]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">{{ __('Simulé') }}</p>
                            <p class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">{{ number_format($simulation['simulated']['score'], 0) }}</p>
                            <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $severityBadge($simulation['simulated']['severity']) }}">{{ $severityLabel($simulation['simulated']['severity']) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="rounded-lg border border-red-500/15 bg-white p-3 dark:border-white/10 dark:bg-[#1b2923]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-red-600 dark:text-red-300">{{ __('Nouvelles constatations') }}</p>
                            <ul class="mt-2 space-y-1 text-xs text-ink/75 dark:text-white/75">
                                @forelse($simulation['findings']['new'] as $finding)
                                    <li>+ {{ $finding['label'] }} (+{{ rtrim(rtrim(number_format((float) $finding['contribution'], 2, '.', ''), '0'), '.') }})</li>
                                @empty
                                    <li class="italic text-ink/40 dark:text-white/40">{{ __('Aucune') }}</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="rounded-lg border border-ink/5 bg-white p-3 dark:border-white/10 dark:bg-[#1b2923]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-ink/50 dark:text-white/50">{{ __('Constatations maintenues') }}</p>
                            <ul class="mt-2 space-y-1 text-xs text-ink/75 dark:text-white/75">
                                @forelse($simulation['findings']['remaining'] as $finding)
                                    <li>= {{ $finding['label'] }}</li>
                                @empty
                                    <li class="italic text-ink/40 dark:text-white/40">{{ __('Aucune') }}</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="rounded-lg border border-emerald-500/15 bg-white p-3 dark:border-white/10 dark:bg-[#1b2923]">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-300">{{ __('Levrées par l’hypothèse') }}</p>
                            <ul class="mt-2 space-y-1 text-xs text-ink/75 dark:text-white/75">
                                @forelse($simulation['findings']['resolved'] as $finding)
                                    <li>− {{ $finding['label'] }}</li>
                                @empty
                                    <li class="italic text-ink/40 dark:text-white/40">{{ __('Aucune') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- Historique & comparaison --}}
        <section class="rounded-2xl border border-ink/5 bg-white p-5 shadow-lg shadow-ink/[0.03] dark:border-white/10 dark:bg-[#1b2923]">
            <div class="mb-4">
                <h3 class="font-fraunces text-lg font-bold text-ink dark:text-white">{{ __('Historique & comparaison') }}</h3>
                <p class="text-xs text-ink/50 dark:text-white/50">{{ __('Les évaluations enregistrées ne sont jamais écrasées : la comparaison exige une preuve (constatation levée).') }}</p>
            </div>

            @if($comparison)
                @php($meta = $conclusionMeta[$comparison['conclusion']] ?? $conclusionMeta['stable'])
                <div class="mb-4 rounded-xl border p-4 {{ $meta['class'] }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold">{{ $meta['label'] }}</p>
                        <p class="font-mono text-xs">
                            {{ rtrim(rtrim(number_format($comparison['before']['score'], 2, '.', ''), '0'), '.') }} → {{ rtrim(rtrim(number_format($comparison['after']['score'], 2, '.', ''), '0'), '.') }}
                            ({{ $comparison['delta']['score'] > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($comparison['delta']['score'], 2, '.', ''), '0'), '.') }})
                        </p>
                    </div>

                    @if($comparison['conclusion'] === 'improvement_unconfirmed')
                        <p class="mt-2 text-xs">{{ __('Le score a baissé mais aucune constatation n’a été levée : vérifiez l’intégrité des données avant de conclure à une amélioration.') }}</p>
                    @endif

                    @if(!empty($comparison['evidence']))
                        <ul class="mt-3 space-y-1 text-xs">
                            @foreach($comparison['evidence'] as $proof)
                                <li>✓ {{ $proof['label'] ?? $proof['rule'] }} — {{ $proof['source_label'] ?? __('source inconnue') }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(!empty($comparison['new']))
                        <p class="mt-3 text-xs font-semibold">{{ __(':new nouvelle(s) constatation(s) apparue(s).', ['new' => count($comparison['new'])]) }}</p>
                    @endif
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="px-3 py-2">{{ __('Date') }}</th>
                            <th class="px-3 py-2">{{ __('Score') }}</th>
                            <th class="px-3 py-2">{{ __('Sévérité') }}</th>
                            <th class="px-3 py-2">{{ __('Complétude') }}</th>
                            <th class="px-3 py-2">{{ __('Recommandation') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $entry)
                            <tr>
                                <td class="px-3 py-3 whitespace-nowrap font-mono text-xs text-ink/70 dark:text-white/70">{{ $entry->created_at?->isoFormat('DD/MM/YYYY HH:mm') }}</td>
                                <td class="px-3 py-3 font-bold text-ink dark:text-white">{{ number_format((float) $entry->score, 0) }}<span class="text-xs font-medium text-ink/40">/100</span></td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $severityBadge($entry->severity) }}">{{ $severityLabel($entry->severity) }}</span>
                                </td>
                                <td class="px-3 py-3 text-ink/60 dark:text-white/60">{{ number_format((float) $entry->data_completeness, 0) }}%</td>
                                <td class="px-3 py-3 text-ink/70 dark:text-white/70">{{ $entry->recommendation ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-sm italic text-ink/40 dark:text-white/40">
                                    {{ __('Aucune évaluation enregistrée. Utilisez « Enregistrer l’évaluation » pour créer une référence.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-dashboard-layout>
