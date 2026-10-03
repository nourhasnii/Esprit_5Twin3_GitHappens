@extends('layouts.stock')
@use('App\Support\Fmt')
@use('App\Support\BatchAttributes')
@use('App\Enums\RecommendationStatus')

@section('title', 'Recommandation n° '.$recommendation->id)
@section('eyebrow', 'Optimization workspace')
@section('description', 'Destination du lot '.BatchAttributes::code($recommendation->batch).', calculée le '.$recommendation->created_at->format('d/m/Y à H:i').'.')
@section('page-action')
    <a class="btn btn-ghost" href="{{ route('optimization.index') }}">Retour aux recommandations</a>
    <span class="tag tag-{{ $recommendation->status->value }}">{{ $recommendation->status->label() }}</span>
@endsection

@section('content')
@php
    $r = $recommendation;
    $explanation = $r->explanation ?? [];
    $best = $r->recommendedCandidate();
    // J− recalculé à la date du jour (la recommandation garde celui du moment du calcul)
    $daysNow = BatchAttributes::daysToExpiry($r->batch);
    $daysLabel = match (true) {
        $daysNow === null => null,
        $daysNow < 0 => 'dépassée',
        $r->days_to_expiry !== null && $r->days_to_expiry !== $daysNow => 'J−'.$daysNow.', J−'.$r->days_to_expiry.' au calcul',
        default => 'J−'.$daysNow,
    };
    $candidates = collect($r->candidates);
    $expiry = BatchAttributes::expiryDate($r->batch);
    $tone = $r->score >= 70 ? 'var(--pine)' : ($r->score >= 40 ? '#c48a1a' : 'var(--brick)');
    $rank = 0;
@endphp

<section class="panel verdict">
    <div class="dial" style="--p: {{ $r->score }}; --dial: {{ $tone }};" role="img" aria-label="Score {{ Fmt::n($r->score) }} sur 100">
        <div class="dial-ring"></div>
        <div class="dial-value"><strong>{{ Fmt::n($r->score) }}</strong><span>sur 100</span></div>
    </div>
    <div class="verdict-body">
        <p class="verdict-summary">{{ $explanation['summary'] ?? '' }}</p>
        <div class="route">
            <div><span>Depuis</span><b>{{ $r->sourceSite?->name ?? '—' }}</b></div>
            <svg aria-hidden="true" viewBox="0 0 40 12" width="40" height="12"><path d="M0 6h36M31 1l5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
            <div><span>Vers</span><b>{{ $r->recommendedSite?->name ?? 'Aucun site faisable' }}</b></div>
        </div>
        <dl class="facts">
            <div><dt>Produit</dt><dd>{{ $r->product?->name }}</dd></div>
            <div><dt>Quantité</dt><dd>{{ Fmt::q($r->quantity) }} u.</dd></div>
            <div><dt>DLC</dt><dd>{{ $expiry ? $expiry->format('d/m/Y') : 'Non renseignée' }}@if ($daysLabel) <span class="muted">({{ $daysLabel }})</span>@endif</dd></div>
            <div><dt>Trajet</dt><dd>{{ $r->distance_km !== null ? Fmt::n($r->distance_km).' km' : '—' }}</dd></div>
            <div><dt>Émissions</dt><dd>{{ $r->co2_kg !== null ? Fmt::n($r->co2_kg, 1).' kg CO₂e' : '—' }}</dd></div>
        </dl>
    </div>
</section>

@if ($lowScore)
    <div class="notice notice-warning" role="alert">
        @if ($lowScore['no_destination'])
            <p class="notice-title">Aucune destination possible pour ce lot.</p>
        @else
            <p class="notice-title">Destination peu satisfaisante : score inférieur à {{ Fmt::n($lowScore['threshold']) }}/100.</p>
            <p style="margin:4px 0 0">Même le meilleur site reste un compromis. Point le plus faible : <b>{{ $lowScore['weakest'] }}</b>.</p>
        @endif
        @if ($lowScore['advice'])
            <p style="margin:6px 0 0">{{ $lowScore['advice'] }}</p>
        @endif
        @if ($lowScore['absorbable'] !== null)
            <p style="margin:6px 0 0">
                @if ($lowScore['absorbable'] <= 0)
                    Si chaque magasin vend d’abord son stock actuel, <b>aucun ne peut écouler ce lot avant la DLC</b>.
                    @if ($rescue && ($rescue['applicable'] ?? false))
                        Le plan ci-dessous applique la règle FEFO : ce lot, le plus proche de sa date limite, est mis en avant et vendu en priorité.
                    @endif
                @else
                    Si chaque magasin vend d’abord son stock actuel, l’ensemble des sites peut écouler environ <b>{{ Fmt::q($lowScore['absorbable']) }} u.</b> de ce lot avant la DLC, sur {{ Fmt::q($r->quantity) }} u.
                    @if ($lowScore['best_absorbable'] !== null)
                        {{ $r->recommendedSite?->name }} peut en absorber environ {{ Fmt::q($lowScore['best_absorbable']) }} u.
                    @endif
                @endif
            </p>
        @endif
        @if ($rescue && ($rescue['applicable'] ?? false) && ! $rescue['applied'])
            <a class="btn btn-primary" href="#plan-anti-gaspillage">Voir le plan anti-gaspillage ↓</a>
        @endif
        @if ($lowScore['retry_url'] && $r->status === RecommendationStatus::Pending)
            <a class="btn btn-ghost" href="{{ $lowScore['retry_url'] }}">Relancer avec {{ Fmt::q($lowScore['best_absorbable']) }} u.</a>
        @endif
    </div>
@endif

@if ($rescue)
    <section class="panel rescue" id="plan-anti-gaspillage">
        <div class="panel-head">
            <div>
                <h2><span aria-hidden="true">🌱</span> Plan anti-gaspillage</h2>
                <p class="muted small">
                    @if ($rescue['applied'])
                        Appliqué le {{ $r->rescue_applied_at->format('d/m/Y à H:i') }}@if ($r->rescuer) par {{ $r->rescuer->name }}@endif. Les transferts et les dons ci-dessous ont été enregistrés dans les mouvements.
                    @else
                        Aucun site ne peut écouler seul ce lot avant sa DLC. Le moteur combine trois leviers pour en sauver le maximum, du plus rentable au plus solidaire.
                    @endif
                </p>
            </div>
            @if ($rescue['applied'])
                <span class="tag tag-rescued">Appliqué</span>
            @endif
        </div>

        @error('rescue')
            <div class="notice notice-error" role="alert">{{ $message }}</div>
        @enderror

        @if (! $rescue['applicable'])
            <p>{{ $rescue['reason'] }}</p>
        @else
            @php
                $impact = $rescue['impact'];
                $actions = collect($rescue['transfers'])->concat($rescue['donations']);
            @endphp

            <div class="rescue-summary">
                <div class="rescue-compare">
                    <div class="rescue-case is-before">
                        <span>Sans plan</span>
                        <strong>{{ Fmt::q($impact['loss_without_plan']) }} u.</strong>
                        <span>perdues à la DLC</span>
                    </div>
                    <span class="rescue-arrow" aria-hidden="true">→</span>
                    <div class="rescue-case is-after">
                        <span>Avec le plan</span>
                        <strong>{{ Fmt::q($impact['loss_with_plan']) }} u.</strong>
                        <span>perdues</span>
                    </div>
                </div>
                <dl class="rescue-impact">
                    <div><dt>Nourriture sauvée</dt><dd>{{ Fmt::n($impact['saved_kg'], 1) }} kg</dd></div>
                    <div><dt>Repas offerts</dt><dd>{{ Fmt::q($impact['meals']) }}</dd></div>
                    <div><dt>CO₂e évité, transport déduit</dt><dd>{{ Fmt::n($impact['co2_net_kg'], 1) }} kg</dd></div>
                </dl>
            </div>

            <ol class="rescue-steps">
                @foreach ($rescue['steps'] as $step)
                    <li @class(['rescue-step', 'is-empty' => $step['quantity'] <= 0, 'is-loss' => $step['level'] === 4])>
                        <span class="step-num" aria-hidden="true">{{ $step['level'] <= 3 ? $step['level'] : '!' }}</span>
                        <div>
                            <p class="step-title">{{ $step['title'] }} <b>· {{ Fmt::q($step['quantity']) }} u.</b></p>
                            <p class="step-text">{{ $step['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            @if ($actions->isNotEmpty())
                <div class="table-wrap" style="margin-bottom:0">
                    <table>
                        <thead>
                            <tr>
                                <th>Destination</th>
                                <th>Action</th>
                                <th class="num">Quantité</th>
                                <th>Promotion</th>
                                <th class="num">Trajet</th>
                                <th class="num">CO₂e transport</th>
                                @if ($rescue['applied'])<th></th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($actions as $a)
                                <tr>
                                    <td><b>{{ $a['site_name'] }}</b>@if ($a['city'])<br><span class="muted small">{{ $a['city'] }}</span>@endif</td>
                                    <td>
                                        @switch($a['kind'])
                                            @case('keep') <span class="tag tag-in">Vendre sur place</span> @break
                                            @case('transfer') <span class="tag tag-transfer">Transfert</span> @break
                                            @default <span class="tag tag-don">Don</span>
                                        @endswitch
                                    </td>
                                    <td class="num"><b>{{ Fmt::q($a['quantity']) }} u.</b></td>
                                    <td>
                                        @if (($a['discount'] ?? 0) > 0)
                                            −{{ $a['discount'] }} % <span class="muted small">(+{{ Fmt::q($a['promo_quantity']) }} u., {{ Fmt::n($a['daily'], 1) }} → {{ Fmt::n($a['boosted_daily'], 1) }} u./jour)</span>
                                        @elseif ($a['kind'] === 'donation')
                                            <span class="muted small">≈ {{ Fmt::q($a['meals']) }} repas</span>
                                        @else
                                            <span class="muted">—</span>
                                        @endif
                                    </td>
                                    <td class="num">{{ $a['kind'] === 'keep' ? '—' : Fmt::n($a['distance_km']).' km' }}</td>
                                    <td class="num">{{ Fmt::n($a['co2_kg'], 1) }} kg</td>
                                    @if ($rescue['applied'])
                                        <td>
                                            @if ($a['kind'] === 'donation')
                                                <a href="{{ route('optimization.donation', [$r, $a['site_id']]) }}" target="_blank">Bon de don</a>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="rescue-map" class="map is-small" aria-label="Carte du plan anti-gaspillage"></div>
                <div class="map-legend">
                    <span><i style="background:#12302b"></i>Origine</span>
                    <span><i style="background:#0f6b58"></i>Transfert vers un magasin</span>
                    <span><i style="background:#a8325e"></i>Don à une association</span>
                    <span><i class="line dashed" style="border-color:#a8325e"></i>Trajet du don</span>
                </div>
            @endif

            @foreach ($rescue['notes'] ?? [] as $note)
                <p class="muted small" style="margin-top:12px">{{ $note }}</p>
            @endforeach

            @if (! $rescue['applied'] && $actions->where('kind', '!=', 'keep')->isNotEmpty())
                <form method="post" action="{{ route('optimization.rescue', $r) }}" class="rescue-apply"
                      onsubmit="return confirm('Appliquer le plan ? Les transferts et les dons seront enregistrés dans les mouvements de stock.');">
                    @csrf
                    <button type="submit" class="btn btn-primary">Appliquer le plan anti-gaspillage</button>
                    <p class="hint">Les transferts et les sorties « don » sont enregistrés immédiatement, depuis {{ $r->sourceSite?->name }}. La promotion est à mettre en place dans les magasins concernés.</p>
                </form>
            @endif
        @endif
    </section>
@endif

<div class="split">
    <section class="panel">
        <h2>Pourquoi ce site</h2>
        <ul class="reasons">
            @foreach ($explanation['points'] ?? [] as $point)
                <li>{{ $point }}</li>
            @endforeach
        </ul>
        @if (! empty($explanation['excluded']))
            <h3 style="margin-top:18px">Sites écartés</h3>
            <ul class="reasons excluded">
                @foreach ($explanation['excluded'] as $name => $why)
                    <li><b>{{ $name }}</b> : {{ implode(' ; ', $why) }}.</li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="panel">
        <h2>Pondération appliquée</h2>
        <p class="muted small" style="margin:4px 0 14px">Part de chaque critère dans le score final.</p>
        <dl class="weights">
            @foreach ($criteria as $key => $label)
                @php $w = ($r->weights[$key] ?? 0) * 100; @endphp
                <div>
                    <dt>{{ $label }}</dt>
                    <dd><span class="bar"><span style="width: {{ round($w) }}%"></span></span>{{ Fmt::n($w) }} %</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>

@if (count($mapPoints) > 1)
    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Carte du trajet</h2>
                <p class="muted small">Chaque site évalué, coloré selon son score. Cliquez sur un point pour voir la distance, la durée et les émissions.</p>
            </div>
        </div>
        <div id="reco-map" class="map" aria-label="Carte du trajet recommandé"></div>
        <div class="map-legend">
            <span><i style="background:#12302b"></i>Origine</span>
            <span><i class="line" style="border-color:#0f6b58"></i>Trajet recommandé</span>
            @if ($r->chosen_site_id && $r->chosen_site_id !== $r->recommended_site_id)
                <span><i class="line dashed" style="border-color:#245d8a"></i>Destination retenue</span>
            @endif
            <span><i style="background:#0f6b58"></i>Score ≥ 70</span>
            <span><i style="background:#c48a1a"></i>40 à 70</span>
            <span><i style="background:#a3261d"></i>Moins de 40</span>
            <span><i style="background:#8a9a95"></i>Exclu</span>
        </div>
    </section>
@endif

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Classement des sites</h2>
            <p class="muted small">Notes de 0 à 100 par critère. Ouvrez un site pour voir le détail du calcul.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table class="ranking">
            <thead>
            <tr>
                <th class="num">Rang</th>
                <th>Site</th>
                <th class="num">Score</th>
                @foreach ($criteria as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach ($candidates as $c)
                @php if ($c['feasible']) { $rank++; } @endphp
                <tr @class(['is-off' => ! $c['feasible'], 'is-pick' => $c['site_id'] === $r->recommended_site_id])>
                    <td class="num">{{ $c['feasible'] ? $rank : '—' }}</td>
                    <td class="site-cell">
                        <details>
                            <summary><b>{{ $c['site_name'] }}</b>@if ($c['site_id'] === $r->chosen_site_id && $r->chosen_site_id !== $r->recommended_site_id) <span class="tag tag-modified">Retenu</span>@endif</summary>
                            <ul class="detail-list">
                                @foreach ($criteria as $key => $label)
                                    @isset($c['reasons'][$key])
                                        <li><b>{{ $label }}</b> : {{ $c['reasons'][$key] }}.</li>
                                    @endisset
                                @endforeach
                            </ul>
                        </details>
                        @if (! $c['feasible'])
                            <div class="small" style="color:var(--brick)">{{ implode(' ; ', $c['blocking']) }}</div>
                        @elseif (isset($c['metrics']['distance_km']))
                            <div class="muted small">{{ $c['city'] ?? $c['site_type'] }}, {{ Fmt::n($c['metrics']['distance_km']) }} km</div>
                        @endif
                    </td>
                    <td class="num total">{{ $c['feasible'] ? Fmt::n($c['total']) : '—' }}</td>
                    @foreach ($criteria as $key => $label)
                        @php $s = $c['scores'][$key] ?? 0; @endphp
                        <td>
                            <span class="mini" title="{{ $c['reasons'][$key] ?? '' }}"><span style="width: {{ $s }}%"></span></span>
                            <span class="mini-value">{{ Fmt::n($s) }}</span>
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>

@if ($r->status === RecommendationStatus::Pending)
    <section class="panel">
        <h2>Votre décision</h2>
        <p class="muted small" style="margin:4px 0 16px">La recommandation reste une aide : vous gardez la main sur la destination finale.</p>
        <form method="POST" action="{{ route('optimization.decide', $r) }}" id="decision-form">
            @csrf
            @php $defaultAction = old('action', $r->recommended_site_id ? 'accept' : 'modify'); @endphp
            <fieldset class="choices">
                <legend class="sr-only">Action</legend>
                <label class="choice">
                    <input type="radio" name="action" value="accept" @checked($defaultAction === 'accept') @disabled(! $r->recommended_site_id)>
                    <b>Valider</b>
                    <small>Envoyer vers {{ $r->recommendedSite?->name ?? 'le site recommandé' }}</small>
                </label>
                <label class="choice">
                    <input type="radio" name="action" value="modify" @checked($defaultAction === 'modify')>
                    <b>Choisir un autre site</b>
                    <small>Remplacer la destination proposée</small>
                </label>
                <label class="choice">
                    <input type="radio" name="action" value="reject" @checked($defaultAction === 'reject')>
                    <b>Rejeter</b>
                    <small>Ne pas déplacer ce lot</small>
                </label>
            </fieldset>

            <div class="form-grid">
                <div class="field" data-when="modify">
                    <label for="chosen_site_id">Site retenu</label>
                    <select id="chosen_site_id" name="chosen_site_id">
                        <option value="">Choisir un site</option>
                        @foreach ($candidates->where('site_id', '!=', $r->recommended_site_id) as $c)
                            <option value="{{ $c['site_id'] }}" @selected(old('chosen_site_id') == $c['site_id'])>
                                {{ $c['site_name'] }} ({{ $c['feasible'] ? 'score '.Fmt::n($c['total']) : 'écarté : '.implode(', ', $c['blocking']) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field wide">
                    <label for="note">Justification</label>
                    <textarea id="note" name="note" maxlength="1000" placeholder="Ex. : le magasin de Nabeul a une promotion prévue cette semaine.">{{ old('note') }}</textarea>
                    <p class="hint">Obligatoire si vous choisissez un autre site ou rejetez la proposition. Elle est conservée avec la décision.</p>
                </div>
                <div class="field wide" data-when="accept modify">
                    <input type="hidden" name="create_movement" value="0">
                    <label class="check"><input type="checkbox" name="create_movement" value="1" @checked(old('create_movement', '1') === '1')> Enregistrer le transfert de stock maintenant</label>
                </div>
            </div>
            <div class="form-foot">
                <button class="btn btn-primary" type="submit">Enregistrer la décision</button>
            </div>
        </form>
    </section>
@else
    <section class="panel">
        <h2>Décision</h2>
        <dl class="facts" style="margin-top:12px">
            <div><dt>Statut</dt><dd>{{ $r->status->label() }}</dd></div>
            @if ($r->chosenSite)<div><dt>Destination retenue</dt><dd>{{ $r->chosenSite->name }}</dd></div>@endif
            <div><dt>Par</dt><dd>{{ $r->decider?->name ?? '—' }}, le {{ $r->decided_at?->format('d/m/Y à H:i') }}</dd></div>
            @if ($r->stockMovement)
                <div><dt>Transfert</dt><dd><a href="{{ route('stock-movements.index', ['batch_id' => $r->batch_id]) }}">{{ $r->stockMovement->reference }}</a></dd></div>
            @endif
        </dl>
        @if ($r->decision_note)
            <p style="margin-top:14px; max-width:70ch"><b>Justification :</b> {{ $r->decision_note }}</p>
        @endif
    </section>
@endif
@endsection

@push('styles')
<style>
    .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    .verdict { display: flex; gap: 32px; align-items: center; flex-wrap: wrap; padding: 26px 28px; }
    .dial { position: relative; width: 164px; height: 164px; flex: none; }
    .dial-ring {
        position: absolute; inset: 0; border-radius: 50%;
        background: conic-gradient(var(--dial) calc(var(--p) * 1%), var(--slot) 0);
        -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 18px), #000 calc(100% - 17px)), repeating-conic-gradient(#000 0 15deg, transparent 15deg 18deg);
        -webkit-mask-composite: source-in;
        mask: radial-gradient(farthest-side, transparent calc(100% - 18px), #000 calc(100% - 17px)), repeating-conic-gradient(#000 0 15deg, transparent 15deg 18deg);
        mask-composite: intersect;
    }
    .dial-value { position: absolute; inset: 0; display: grid; place-content: center; text-align: center; }
    .dial-value strong { font-size: 2.8rem; font-weight: 800; line-height: 1; letter-spacing: -0.03em; font-variant-numeric: tabular-nums; }
    .dial-value span { color: var(--ink-soft); font-size: 0.85rem; margin-top: 4px; }
    .verdict-body { flex: 1; min-width: 280px; display: grid; gap: 16px; }
    .verdict-summary { font-size: 1.25rem; font-weight: 700; line-height: 1.35; max-width: 46ch; }
    .route { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; color: var(--ink-soft); }
    .route div { display: grid; }
    .route span { font-size: 0.8rem; }
    .route b { color: var(--ink); font-size: 1rem; }
    .facts { display: flex; flex-wrap: wrap; gap: 8px 28px; margin: 0; }
    .facts dt { font-size: 0.8rem; color: var(--ink-soft); }
    .facts dd { margin: 0; font-weight: 600; font-variant-numeric: tabular-nums; }
    .split { display: grid; grid-template-columns: minmax(0, 3fr) minmax(260px, 2fr); gap: 20px; align-items: start; }
    .reasons { margin: 12px 0 0; padding-left: 18px; display: grid; gap: 8px; max-width: 75ch; }
    .reasons.excluded { color: var(--ink-soft); font-size: 0.92rem; }
    .weights { margin: 0; display: grid; gap: 10px; }
    .weights div { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center; }
    .weights dt { font-size: 0.9rem; }
    .weights dd { margin: 0; display: flex; align-items: center; gap: 8px; font-variant-numeric: tabular-nums; font-size: 0.88rem; font-weight: 600; }
    .weights .bar { width: 90px; height: 8px; background: var(--slot); border-radius: 4px; overflow: hidden; display: inline-block; }
    .weights .bar span { display: block; height: 100%; background: var(--pine); }
    .ranking td { vertical-align: middle; }
    .ranking tr.is-pick td { background: var(--pine-wash); }
    .ranking tr.is-pick td:first-child { box-shadow: inset 4px 0 0 var(--pine); }
    .ranking .total { font-size: 1.05rem; font-weight: 800; }
    .site-cell { min-width: 220px; }
    .site-cell summary { cursor: pointer; }
    .detail-list { margin: 8px 0 4px; padding-left: 18px; font-size: 0.86rem; color: var(--ink-soft); display: grid; gap: 4px; max-width: 60ch; }
    .mini { display: inline-block; width: 56px; height: 7px; background: var(--slot); border-radius: 4px; overflow: hidden; vertical-align: middle; }
    .mini span { display: block; height: 100%; background: var(--pine); }
    tr.is-off .mini span { background: #9aaaa5; }
    .mini-value { font-variant-numeric: tabular-nums; font-size: 0.85rem; margin-left: 6px; display: inline-block; min-width: 24px; }
    .field[data-when][hidden] { display: none; }
    @media (max-width: 820px) { .split { grid-template-columns: 1fr; } }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('decision-form');
        if (!form) return;
        const sync = () => {
            const action = form.querySelector('input[name="action"]:checked')?.value;
            form.querySelectorAll('[data-when]').forEach((el) => {
                const visible = el.dataset.when.split(' ').includes(action);
                el.hidden = !visible;
                el.querySelectorAll('input, select').forEach((input) => { input.disabled = !visible; });
            });
        };
        form.querySelectorAll('input[name="action"]').forEach((radio) => radio.addEventListener('change', sync));
        sync();
    })();
</script>
@endpush

@include('partials.map')

@push('scripts')
<script>
    (() => {
        const points = @json($mapPoints);
        const map = NtMap.create(document.getElementById('reco-map'));
        if (!map) return;

        const c = NtMap.colors;
        const source = points.find((p) => p.role === 'source');
        const scoreColor = (s) => s === null ? c.grey : (s >= 70 ? c.pine : (s >= 40 ? c.amber : c.brick));
        const bounds = [];

        const details = (p) => {
            const lines = [`<b>${NtMap.esc(p.name)}</b>${p.city ? ' <span class="muted">· ' + NtMap.esc(p.city) + '</span>' : ''}`];
            if (p.role === 'source') {
                lines.push('Site d’origine du lot');
            } else {
                if (p.role === 'pick') lines.push('<b style="color:' + c.pine + '">Destination recommandée</b>');
                if (p.role === 'chosen') lines.push('<b style="color:' + c.sky + '">Destination retenue par l’utilisateur</b>');
                lines.push(p.score !== null ? `Score : <b>${NtMap.fmt(p.score, 1)}/100</b>` : `<span style="color:${c.brick}">Exclu : ${NtMap.esc(p.blocking.join(' ; '))}</span>`);
                if (p.distance !== null) lines.push(`${NtMap.fmt(p.distance)} km · ${NtMap.fmt(p.hours, 1)} h · ${NtMap.fmt(p.co2, 1)} kg CO₂e`);
            }
            return lines.join('<br>');
        };

        // Trajets depuis l'origine (ligne droite indicative : la distance affichée est estimée par la route)
        points.filter((p) => p.role === 'pick' || p.role === 'chosen').forEach((p) => {
            if (!source) return;
            L.polyline([[source.lat, source.lng], [p.lat, p.lng]], {
                color: p.role === 'pick' ? c.pine : c.sky,
                weight: p.role === 'pick' ? 5 : 4,
                opacity: .85,
                dashArray: p.role === 'chosen' ? '8 8' : null,
            }).addTo(map);
        });

        // Les points importants sont dessinés en dernier pour rester au-dessus
        const order = { excluded: 0, candidate: 1, chosen: 2, pick: 3, source: 4 };
        points.slice().sort((a, b) => order[a.role] - order[b.role]).forEach((p) => {
            const main = p.role === 'source' || p.role === 'pick' || p.role === 'chosen';
            const marker = L.circleMarker([p.lat, p.lng], {
                radius: main ? 11 : 8,
                color: '#fff',
                weight: main ? 3 : 2,
                fillColor: p.role === 'source' ? c.ink : scoreColor(p.score),
                fillOpacity: p.role === 'excluded' ? .55 : .95,
            }).bindPopup(details(p)).addTo(map);

            const label = p.role === 'source' ? 'Origine' : (p.score !== null ? NtMap.fmt(p.score) : 'Exclu');
            marker.bindTooltip(NtMap.esc(label), {
                permanent: true, direction: 'right', offset: [main ? 12 : 9, 0], className: 'map-label',
            });

            bounds.push([p.lat, p.lng]);
        });

        NtMap.fit(map, bounds);
    })();
</script>
@endpush

@push('styles')
<style>
    .rescue { border-color: #c9e0b4; }
    .rescue h2 span { margin-right: 4px; }
    .rescue-summary { display: flex; flex-wrap: wrap; gap: 16px 32px; align-items: center; margin: 6px 0 22px; }
    .rescue-compare { display: flex; align-items: center; gap: 14px; }
    .rescue-case { display: grid; text-align: center; padding: 12px 18px; border-radius: 9px; min-width: 130px; }
    .rescue-case span { font-size: .8rem; }
    .rescue-case strong { font-size: 1.7rem; font-weight: 800; line-height: 1.2; font-variant-numeric: tabular-nums; }
    .rescue-case.is-before { background: var(--brick-wash); color: var(--brick); }
    .rescue-case.is-after { background: var(--leaf-wash); color: var(--leaf); }
    .rescue-arrow { font-size: 1.4rem; color: var(--ink-soft); }
    .rescue-impact { display: flex; flex-wrap: wrap; gap: 8px 28px; margin: 0; }
    .rescue-impact dt { font-size: .8rem; color: var(--ink-soft); }
    .rescue-impact dd { margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--leaf); font-variant-numeric: tabular-nums; }
    .rescue-steps { list-style: none; margin: 0 0 20px; padding: 0; display: grid; gap: 10px; }
    .rescue-step { display: flex; gap: 14px; align-items: flex-start; padding: 12px 14px; border: 1px solid var(--rule); border-radius: 9px; }
    .rescue-step.is-empty { opacity: .6; }
    .rescue-step.is-loss { border-color: #efc1ba; background: var(--brick-wash); }
    .step-num { flex: none; display: grid; place-items: center; width: 30px; height: 30px; border-radius: 50%; background: var(--leaf); color: #fff; font-weight: 800; }
    .rescue-step.is-loss .step-num { background: var(--brick); }
    .step-title { margin: 0; font-weight: 700; }
    .step-text { margin: 2px 0 0; color: var(--ink-soft); max-width: 90ch; }
    .tag-don { background: var(--rose-wash); color: var(--rose); }
    #rescue-map { margin-top: 18px; }
    .rescue-apply { margin-top: 20px; }
    .rescue-apply .hint { margin-top: 8px; }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const plan = @json($rescue);
        const el = document.getElementById('rescue-map');
        if (!plan || !plan.applicable || !el) return;

        const map = NtMap.create(el);
        if (!map) return;

        const c = NtMap.colors;
        const rose = '#a8325e';
        const src = plan.source;
        const bounds = [[src.lat, src.lng]];

        // L'origine est dessinée en premier ; les magasins ensuite, puis les associations,
        // plus petites, par-dessus : un magasin et une association voisins restent tous deux visibles.
        L.circleMarker([src.lat, src.lng], { radius: 12, color: '#fff', weight: 3, fillColor: c.ink, fillOpacity: 1 })
            .bindTooltip('Origine', { permanent: true, direction: 'bottom', offset: [0, 12], className: 'map-label' })
            .bindPopup(`<b>${NtMap.esc(src.name)}</b><br>Site d’origine du lot`)
            .addTo(map);

        const stop = (p, color, text, isDonation) => {
            if (p.kind !== 'keep') {
                L.polyline([[src.lat, src.lng], [p.lat, p.lng]], {
                    color, weight: isDonation ? 3 : 5, opacity: .85, dashArray: isDonation ? '6 8' : null,
                }).addTo(map);
            }
            const radius = isDonation ? 7 : 11;
            L.circleMarker([p.lat, p.lng], { radius, color: '#fff', weight: 3, fillColor: color, fillOpacity: 1 })
                .bindTooltip(NtMap.esc(NtMap.fmt(p.quantity) + ' u.'), {
                    permanent: true,
                    direction: isDonation ? 'right' : 'left',
                    offset: [isDonation ? radius + 2 : -(radius + 2), 0],
                    className: 'map-label',
                })
                .bindPopup(`<b>${NtMap.esc(p.site_name)}</b><br>${NtMap.esc(text)}`)
                .addTo(map);
            bounds.push([p.lat, p.lng]);
        };

        plan.transfers.forEach((t) => stop(t, c.pine,
            (t.kind === 'keep' ? 'Vendu sur place' : 'Transfert') + ` de ${NtMap.fmt(t.quantity)} u.` + (t.discount ? ` avec −${t.discount} %` : ''), false));
        plan.donations.forEach((d) => stop(d, rose, `Don de ${NtMap.fmt(d.quantity)} u., environ ${NtMap.fmt(d.meals)} repas`, true));

        NtMap.fit(map, bounds, { maxZoom: 12 });
    })();
</script>
@endpush
