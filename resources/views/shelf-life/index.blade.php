@extends('layouts.stock')
@use('App\Support\Fmt')
@use('App\Services\ShelfLife\ShelfLifePredictor')
@use('Carbon\CarbonImmutable')

@php
    $riskTag = [
        ShelfLifePredictor::RISK_CRITICAL => 'tag-out',
        ShelfLifePredictor::RISK_ATTENTION => 'tag-expiring',
        ShelfLifePredictor::RISK_EXPIRED => 'tag-expired',
        ShelfLifePredictor::RISK_OK => 'tag-ok',
        ShelfLifePredictor::RISK_UNMONITORED => '',
    ];
@endphp

@section('eyebrow', __('Dynamic shelf life'))
@section('title', __('DLC dynamique'))
@section('description', __('La DLC imprimée suppose une chaîne du froid parfaite. L’IA lit les températures du transport (module Chaîne du froid) et calcule la vraie date limite de chaque lot avec un modèle de microbiologie prévisionnelle.'))

@section('content')
<form class="filters" method="GET" action="{{ route('shelf-life.index') }}">
    <div class="field">
        <label for="f-scope">{{ __('Lots') }}</label>
        <select id="f-scope" name="scope">
            <option value="stock" @selected($scope === 'stock')>{{ __('En stock dans le module') }}</option>
            <option value="all" @selected($scope === 'all')>{{ __('Tous les lots suivis par la chaîne du froid') }}</option>
        </select>
    </div>
    <div class="field">
        <label for="f-risk">{{ __('Niveau de risque') }}</label>
        <select id="f-risk" name="risk">
            <option value="">{{ __('Tous') }}</option>
            @foreach (ShelfLifePredictor::RISK_LABELS as $k => $label)
                <option value="{{ $k }}" @selected(($filters['risk'] ?? null) === $k)>{{ __($label) }}</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-primary" type="submit">{{ __('Afficher') }}</button>
</form>

<div class="figures">
    <div @class(['figure', 'is-alert' => $counts['at_risk'] > 0])>
        <strong>{{ $counts['at_risk'] }}</strong>
        <span>{{ __('lot(s) dont la durée de vie a été réduite par la chaleur') }}</span>
    </div>
    <div class="figure">
        <strong>{{ Fmt::n($counts['days_lost'], 1) }} j</strong>
        <span>{{ __('de durée de vie perdus au total sur ces lots') }}</span>
    </div>
    <div class="figure">
        <strong>{{ Fmt::n($counts['units_at_risk']) }}</strong>
        <span>{{ __('unités en stock à vendre en priorité ou à contrôler') }}</span>
    </div>
    <div class="figure">
        <strong>{{ $counts['monitored'] }} / {{ $counts['lots'] }}</strong>
        <span>{{ __('lots avec des relevés de température du transport') }}</span>
    </div>
</div>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>{{ __('Vraie durée de vie des lots') }}</h2>
            <p class="muted small">{{ __('Du plus urgent au moins urgent. La DLC réelle est utilisée automatiquement par la rotation des stocks, l’optimisation, l’anti-gaspillage et le jumeau numérique.') }}</p>
        </div>
    </div>

    @if ($rows->isEmpty())
        <p class="muted">{{ __('Aucun lot à afficher. Les lots en stock apparaissent ici ; les relevés de température viennent du module Chaîne du froid.') }}</p>
    @else
        <div class="table-wrap">
            <table class="sl-table">
                <thead>
                    <tr>
                        <th>{{ __('Lot') }}</th>
                        <th>{{ __('Où ?') }}</th>
                        <th>{{ __('DLC imprimée') }}</th>
                        <th>{{ __('DLC réelle prédite') }}</th>
                        <th class="num">{{ __('Vie perdue') }}</th>
                        <th>{{ __('État') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $r)
                        @php
                            $printed = CarbonImmutable::parse($r['printed']);
                            $predicted = CarbonImmutable::parse($r['predicted']);
                            $shorter = $predicted->lt($printed);
                        @endphp
                        <tr>
                            <td>
                                <a class="code" href="{{ route('shelf-life.show', $r['batch_id']) }}">{{ $r['lot'] }}</a>
                                <div class="muted small">{{ $r['product'] }} · {{ $r['profile_label'] }}</div>
                            </td>
                            <td>
                                @if ($r['units'] > 0)
                                    {{ Fmt::q($r['units']) }} u.
                                    <div class="muted small">{{ implode(', ', $r['sites']) }}</div>
                                @else
                                    <span class="muted">{{ __('Hors stock du module') }}</span>
                                @endif
                            </td>
                            <td @class(['strike' => $shorter])>{{ $printed->format('d/m/Y') }}</td>
                            <td>
                                @if ($r['readings'] === 0)
                                    <span class="muted">{{ __('Pas de relevé') }}</span>
                                @else
                                    <b @class(['dlc-short' => $shorter])>{{ $predicted->format('d/m/Y') }}</b>
                                    @if ($shorter)
                                        <span class="tag tag-out">−{{ (int) abs($printed->diffInDays($predicted)) }} j</span>
                                    @endif
                                    @if ($r['earliest'] !== $r['latest'])
                                        <div class="muted small">{{ __('fourchette :m – :m2', ['m' => CarbonImmutable::parse($r['earliest'])->format('d/m'), 'm2' => CarbonImmutable::parse($r['latest'])->format('d/m')]) }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="num">
                                @if ($r['lost_hours'] > 0)
                                    {{ Fmt::n($r['lost_hours'], 1) }} h
                                    <div class="muted small">{{ __(':readings relevé(s)', ['readings' => $r['readings']]) }}</div>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="tag {{ $riskTag[$r['risk']] }}">{{ $r['risk_label'] }}</span>
                                @if ($r['assessment'])
                                    <div class="muted small">{{ $r['assessment']['decision'] === 'accepted' ? __('✓ validée') : __('DLC imprimée gardée') }}</div>
                                @endif
                            </td>
                            <td class="row-actions"><a href="{{ route('shelf-life.show', $r['batch_id']) }}">{{ __('Détail') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section class="panel">
    <h2>{{ __('Comment l’IA calcule la vraie DLC ?') }}</h2>
    <ol class="how">
        <li><b>{{ __('Les températures réelles.') }}</b> {{ __('Chaque relevé du transport (lieu, température, durée) vient du module Chaîne du froid.') }}</li>
        <li><b>{{ __('La vitesse de vieillissement.') }}</b> {{ __('Modèle de la racine carrée de') }} <b>{{ __('Ratkowsky') }}</b>{{ __(', référence en microbiologie prévisionnelle : les bactéries se multiplient à une vitesse proportionnelle à (T − Tmin)². Par rapport au froid prévu : vitesse = ((T − Tmin) / (Tréf − Tmin))².') }}</li>
        <li><b>{{ __('Les heures perdues.') }}</b> {{ __('Une heure à T « consomme » cette vitesse en heures de vie. Exemple (produits laitiers, Tréf 4 °C, Tmin −2 °C) : 1 h à 10 °C = 4 h de vie, donc 3 h perdues.') }}</li>
        <li><b>{{ __('La DLC réelle.') }}</b> {{ __('DLC imprimée moins les heures perdues, arrondie avec prudence (une tranche de :day_rounding_hours h ou plus compte pour un jour). Elle n’est jamais prolongée.', ['day_rounding_hours' => config('stock.shelf_life.day_rounding_hours', 6)]) }}</li>
        <li><b>{{ __('L’incertitude.') }}</b> {{ __('Tmin n’est connu qu’à ± 1,5 °C près : le calcul est refait avec les deux bornes, ce qui donne une fourchette (au plus tôt / au plus tard).') }}</li>
    </ol>
    <div class="table-wrap profiles">
        <table>
            <thead><tr><th>{{ __('Famille de produits') }}</th><th class="num">{{ __('T° de référence') }}</th><th class="num">{{ __('Tmin (fourchette)') }}</th><th class="num">{{ __('T° critique') }}</th></tr></thead>
            <tbody>
                @foreach ($profiles as $profile)
                    <tr>
                        <td>{{ __($profile['label']) }}</td>
                        <td class="num">{{ Fmt::n($profile['reference_temp'], 1) }} °C</td>
                        <td class="num">{{ Fmt::n($profile['t_min'], 1) }} °C ({{ Fmt::n($profile['t_min_range'][0], 1) }} à {{ Fmt::n($profile['t_min_range'][1], 1) }})</td>
                        <td class="num">{{ Fmt::n($profile['critical_temp'], 1) }} °C</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('styles')
<style>
    .sl-table td { vertical-align: top; }
    .sl-table td:first-child { min-width: 220px; }
    .strike { text-decoration: line-through; color: var(--ink-soft); }
    .dlc-short { color: var(--brick); }
    .dark .dlc-short { color: #ffc1c1; }
    .how { margin: 8px 0 16px; padding-left: 20px; display: grid; gap: 8px; line-height: 1.55; }
    .profiles { margin: 0 -22px -20px; border-top: 1px solid var(--rule); }
    .profiles td.num, .profiles th.num { text-align: right; }
</style>
@endpush
