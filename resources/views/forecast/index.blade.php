@extends('layouts.stock')
@use('App\Support\Fmt')
@use('App\Services\Forecast\DemandForecaster')
@use('Carbon\CarbonImmutable')

@section('title', 'Prévisions de la demande')

@section('content')
<div class="page-head">
    <div>
        <h1>Prévisions de la demande</h1>
        <p class="lede">L’IA apprend le rythme des ventes de chaque magasin et tient compte du calendrier tunisien : Ramadan, Aïd, rentrée scolaire, saison estivale. Ces prévisions alimentent le moteur d’optimisation et le plan anti-gaspillage.</p>
    </div>
</div>

<form class="filters" method="GET" action="{{ route('forecast.index') }}">
    <div class="field">
        <label for="f-site">Site</label>
        <select id="f-site" name="site_id">
            @foreach ($sites as $s)
                <option value="{{ $s->id }}" @selected($site?->id === $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-product">Produit</label>
        <select id="f-product" name="product_id">
            @foreach ($products as $p)
                <option value="{{ $p->id }}" @selected($product?->id === $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-from">À partir du</label>
        <input type="date" id="f-from" name="from" value="{{ $from->toDateString() }}">
    </div>
    <div class="field">
        <label for="f-days">Horizon</label>
        <select id="f-days" name="days">
            @foreach ([7, 14, 30] as $d)
                <option value="{{ $d }}" @selected($days === $d)>{{ $d }} jours</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-primary" type="submit">Prévoir</button>
</form>

@if ($shortcuts)
    <p class="shortcuts">
        <span class="muted small">Simuler une période :</span>
        <a @class(['chip', 'is-on' => ! $isSimulation]) href="{{ route('forecast.index', ['site_id' => $site?->id, 'product_id' => $product?->id, 'days' => $days]) }}">Aujourd’hui</a>
        @foreach ($shortcuts as $sc)
            <a @class(['chip', 'is-on' => $from->toDateString() === $sc['from']]) href="{{ route('forecast.index', ['site_id' => $site?->id, 'product_id' => $product?->id, 'days' => $days, 'from' => $sc['from']]) }}">{{ $sc['label'] }} <span class="muted">· {{ $sc['date']->format('d/m/Y') }}</span></a>
        @endforeach
    </p>
@endif

@if (! $forecast || ! $forecast['has_history'])
    <section class="panel">
        <p><b>Pas encore de ventes enregistrées</b> pour {{ $product?->name ?? 'ce produit' }} à {{ $site?->name ?? 'ce site' }} sur les {{ config('stock.forecast.history_days') }} derniers jours : la prévision vaut 0.</p>
        <p class="muted small">Les dons, la casse et les destructions ne comptent pas comme des ventes. Choisissez un magasin qui vend ce produit.</p>
    </section>
@else
    @php
        $f = $forecast;
        $history = array_slice($f['history'], -21);
        $points = [];
        foreach ($history as $h) {
            $points[] = ['kind' => 'h', 'date' => $h['date'], 'qty' => $h['qty'], 'raw' => $h['raw'], 'events' => $h['events']];
        }
        foreach ($f['days'] as $d) {
            $points[] = ['kind' => 'f'] + $d;
        }

        // Géométrie du graphique
        $W = 960; $H = 320; $L = 46; $R = 16; $T = 30; $B = 44;
        $n = count($points);
        $slot = ($W - $L - $R) / max(1, $n);
        $max = max(1, ...array_map(fn ($p) => max($p['qty'], $p['high'] ?? 0), $points)) * 1.12;
        $step = collect([1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000])->first(fn ($s) => $max / $s <= 5) ?? 1000;
        $top = ceil($max / $step) * $step;
        $x = fn ($i) => $L + ($i + 0.5) * $slot;
        $y = fn ($v) => $T + ($H - $T - $B) * (1 - $v / $top);
        $firstForecast = count($history);

        // Bandes d'événements (jours consécutifs avec les mêmes événements)
        $bands = [];
        foreach ($points as $i => $p) {
            $key = implode(' + ', $p['events']);
            if ($key === '') { continue; }
            $last = end($bands);
            if ($last && $last['key'] === $key && $last['to'] === $i - 1) {
                $bands[array_key_last($bands)]['to'] = $i;
            } else {
                $bands[] = ['key' => $key, 'from' => $i, 'to' => $i];
            }
        }

        $line = collect($points)->slice($firstForecast)->map(fn ($p, $i) => round($x($i), 1).','.round($y($p['qty']), 1))->join(' ');
        $band = collect($points)->slice($firstForecast)->map(fn ($p, $i) => round($x($i), 1).','.round($y($p['high']), 1))->join(' ')
            .' '.collect($points)->slice($firstForecast)->reverse()->map(fn ($p, $i) => round($x($i), 1).','.round($y($p['low']), 1))->join(' ');
        $labelEvery = max(1, (int) ceil($n / 12));

        $acc = $f['accuracy'];
        $eventsAhead = collect($f['days'])->flatMap(fn ($d) => $d['events'])->unique()->values();
        $multipliers = collect(config('stock.forecast.events'))->keyBy('label');
    @endphp

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>{{ $product->name }} · {{ $site->name }}</h2>
                <p class="muted small">
                    @if ($isSimulation)
                        Simulation à partir du {{ $from->format('d/m/Y') }} : prévision de {{ Fmt::n($f['total'], 0) }} u. sur {{ $days }} jours, soit {{ Fmt::n($f['average'], 1) }} u./jour en moyenne.
                    @else
                        Prévision de {{ Fmt::n($f['total'], 0) }} u. sur les {{ $days }} prochains jours, soit {{ Fmt::n($f['average'], 1) }} u./jour en moyenne (moyenne simple des ventes passées : {{ Fmt::n($f['simple_average'], 1) }}).
                    @endif
                </p>
            </div>
            @if ($stockout)
                <span class="tag tag-low">Rupture prévue le {{ CarbonImmutable::parse($stockout['date'])->locale('fr')->isoFormat('dddd DD/MM') }}</span>
            @endif
        </div>

        <div class="chart-wrap">
            <svg viewBox="0 0 {{ $W }} {{ $H }}" class="chart" role="img" aria-label="Ventes des 21 derniers jours et prévision">
                @foreach ($bands as $b)
                    <rect x="{{ $L + $b['from'] * $slot }}" y="{{ $T }}" width="{{ ($b['to'] - $b['from'] + 1) * $slot }}" height="{{ $H - $T - $B }}" fill="var(--amber-wash)"/>
                    <text x="{{ $L + $b['from'] * $slot + 4 }}" y="{{ $T - 8 }}" class="c-event">{{ \Illuminate\Support\Str::limit($b['key'], max(8, (int) (($b['to'] - $b['from'] + 1) * $slot / 6.5))) }}</text>
                @endforeach

                @for ($v = 0; $v <= $top; $v += $step)
                    <line x1="{{ $L }}" x2="{{ $W - $R }}" y1="{{ $y($v) }}" y2="{{ $y($v) }}" class="c-grid"/>
                    <text x="{{ $L - 8 }}" y="{{ $y($v) + 4 }}" class="c-axis" text-anchor="end">{{ Fmt::q($v) }}</text>
                @endfor

                @foreach ($points as $i => $p)
                    @if ($p['kind'] === 'h')
                        <rect x="{{ $L + $i * $slot + $slot * 0.18 }}" y="{{ $y($p['qty']) }}" width="{{ $slot * 0.64 }}" height="{{ max(0, $y(0) - $y($p['qty'])) }}" rx="2" class="c-bar">
                            <title>{{ CarbonImmutable::parse($p['date'])->format('d/m') }} : {{ Fmt::q($p['raw']) }} u. vendues</title>
                        </rect>
                        @if ($p['raw'] > $p['qty'] + 0.01)
                            <text x="{{ $x($i) }}" y="{{ $y($p['qty']) - 6 }}" text-anchor="middle" class="c-outlier">▲<title>Vente exceptionnelle : {{ Fmt::q($p['raw']) }} u., ramenée à {{ Fmt::q($p['qty']) }} u.</title></text>
                        @endif
                    @endif
                @endforeach

                <polygon points="{{ $band }}" class="c-band"/>
                <polyline points="{{ $line }}" class="c-line"/>
                @foreach ($points as $i => $p)
                    @if ($p['kind'] === 'f')
                        <circle cx="{{ $x($i) }}" cy="{{ $y($p['qty']) }}" r="3.5" class="c-dot">
                            <title>{{ $p['weekday'] }} {{ CarbonImmutable::parse($p['date'])->format('d/m') }} : {{ Fmt::n($p['qty'], 1) }} u. prévues ({{ Fmt::n($p['low'], 0) }} à {{ Fmt::n($p['high'], 0) }})</title>
                        </circle>
                    @endif
                @endforeach

                <line x1="{{ $L + $firstForecast * $slot }}" x2="{{ $L + $firstForecast * $slot }}" y1="{{ $T - 4 }}" y2="{{ $H - $B }}" class="c-today"/>
                <text x="{{ $L + $firstForecast * $slot + 5 }}" y="{{ $H - $B - 6 }}" class="c-axis">{{ $isSimulation ? '⋯ simulation' : 'aujourd’hui' }}</text>

                @foreach ($points as $i => $p)
                    @if ($i % $labelEvery === 0)
                        <text x="{{ $x($i) }}" y="{{ $H - $B + 16 }}" text-anchor="middle" class="c-axis">{{ CarbonImmutable::parse($p['date'])->format('d/m') }}</text>
                        <text x="{{ $x($i) }}" y="{{ $H - $B + 30 }}" text-anchor="middle" class="c-axis c-soft">{{ DemandForecaster::WEEKDAYS[CarbonImmutable::parse($p['date'])->dayOfWeekIso] }}</text>
                    @endif
                @endforeach
            </svg>
        </div>
        <div class="map-legend">
            <span><i class="sw sw-bar"></i>Ventes réelles (21 derniers jours)</span>
            <span><i class="sw sw-line"></i>Prévision</span>
            <span><i class="sw sw-band"></i>Fourchette probable (80 %)</span>
            <span><i class="sw sw-event"></i>Période spéciale du calendrier</span>
            @if ($f['outliers'])<span><b style="color:var(--brick)">▲</b> Vente exceptionnelle corrigée</span>@endif
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Comment l’IA a calculé cette prévision</h2>
                <p class="muted small">Chaque étape est expliquée : la prévision n’est pas une boîte noire.</p>
            </div>
        </div>

        <ol class="steps">
            <li>
                <span class="step-n">1</span>
                <div>
                    <p class="step-t">Historique analysé</p>
                    <p>{{ $f['history_days'] }} jour(s) de ventes sur les {{ config('stock.forecast.history_days') }} derniers jours.
                        @if ($f['missing_days'] > 0) {{ $f['missing_days'] }} jour(s) sans aucune saisie dans le magasin sont ignorés (magasin fermé ou saisie oubliée), au lieu de compter pour zéro. @endif
                        Les dons, la casse et les destructions ne sont pas comptés comme des ventes.</p>
                </div>
            </li>
            <li>
                <span class="step-n">2</span>
                <div>
                    <p class="step-t">Ventes exceptionnelles</p>
                    @forelse ($f['outliers'] as $o)
                        <p>Le {{ CarbonImmutable::parse($o['date'])->format('d/m') }}, {{ Fmt::q($o['original']) }} u. vendues au lieu d’environ {{ Fmt::n($f['simple_average'], 0) }} habituellement : valeur ramenée à {{ Fmt::n($o['capped'], 0) }} u. pour ne pas fausser la prévision (erreur de saisie ou commande isolée ?).</p>
                    @empty
                        <p>Aucune vente anormale détectée.</p>
                    @endforelse
                </div>
            </li>
            <li>
                <span class="step-n">3</span>
                <div>
                    <p class="step-t">Rythme de la semaine</p>
                    @if (($acc['weekly'] ?? true) && collect($f['weekday_factors'])->contains(fn ($v) => abs($v - 1) > 0.03))
                        <div class="week">
                            @foreach ($f['weekday_factors'] as $wd => $factor)
                                <div class="week-day">
                                    <span class="week-bar"><i style="height: {{ max(8, min(100, 50 * $factor)) }}%" @class(['is-up' => $factor > 1.03, 'is-down' => $factor < 0.97])></i></span>
                                    <b>{{ $factor >= 1 ? '+' : '−' }}{{ Fmt::n(abs($factor - 1) * 100) }} %</b>
                                    <span>{{ DemandForecaster::WEEKDAYS[$wd] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="muted small">Appris sur l’historique, sans règle fixée à l’avance.</p>
                    @else
                        <p>Aucun rythme hebdomadaire fiable dans ces ventes : l’auto-calibrage a désactivé cet effet, qui n’aurait ajouté que du bruit.</p>
                    @endif
                </div>
            </li>
            <li>
                <span class="step-n">4</span>
                <div>
                    <p class="step-t">Niveau et tendance</p>
                    <p>Niveau actuel d’environ <b>{{ Fmt::n($f['level'], 1) }} u./jour</b>, tendance de <b>{{ $f['trend'] >= 0 ? '+' : '−' }}{{ Fmt::n(abs($f['trend']), 2) }} u./jour</b>, amortie avec le temps pour ne pas s’emballer (lissage exponentiel de Holt).</p>
                </div>
            </li>
            <li>
                <span class="step-n">5</span>
                <div>
                    <p class="step-t">Calendrier tunisien</p>
                    @if ($eventsAhead->isEmpty())
                        <p>Aucune période spéciale sur les {{ $days }} jours prévus.@if ($f['category']) Produit reconnu comme « {{ $f['category'] }} ». @endif</p>
                    @else
                        <p>Périodes appliquées pour ce produit {{ $f['category'] ? '(famille « '.$f['category'].' »)' : '' }}{{ $f['coastal'] ? ' dans une ville balnéaire' : '' }} :</p>
                        <ul class="events">
                            @foreach ($eventsAhead as $label)
                                @php($m = $multipliers[$label]['multipliers'] ?? [])
                                @php($coef = (float) ($f['category'] && isset($m[$f['category']]) ? $m[$f['category']] : ($m['*'] ?? 1)))
                                <li><b>{{ $label }}</b> : ventes × {{ Fmt::n($coef, 2) }} ({{ $coef >= 1 ? '+' : '−' }}{{ Fmt::n(abs($coef - 1) * 100) }} %)</li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="muted small">Hypothèses métier modifiables dans <code>config/stock.php</code>. Les dates des fêtes religieuses sont estimées (calendrier lunaire).</p>
                </div>
            </li>
            <li>
                <span class="step-n">6</span>
                <div>
                    <p class="step-t">Précision mesurée</p>
                    @if ($acc)
                        @php($ratio = $acc['error'] > 0 ? $acc['baseline_error'] / $acc['error'] : null)
                        <p>L’IA a rejoué les {{ $acc['test_days'] }} derniers jours comme si elle ne les connaissait pas. Elle se trompe en moyenne de <b>{{ Fmt::n($acc['error'], 1) }} %</b>, contre <b>{{ Fmt::n($acc['baseline_error'], 1) }} %</b> pour une moyenne simple.
                            @if ($ratio && $ratio >= 1.15)
                                Elle est donc <b>{{ Fmt::n($ratio, 1) }} fois plus précise</b>.
                            @elseif ($ratio && $ratio >= 0.85)
                                Les deux méthodes sont comparables sur ces ventes, qui ne suivent pas de rythme marqué.
                            @else
                                Sur ces ventes très irrégulières, la moyenne simple fait un peu mieux : la prévision reste prudente.
                            @endif
                        </p>
                        <p class="muted small">Auto-calibrage : {{ $acc['tried'] }} réglages essayés ; retenu : lissage {{ Fmt::n($acc['alpha'], 2) }}, tendance {{ $acc['beta'] > 0 ? 'activée' : 'désactivée' }}, rythme de la semaine {{ $acc['weekly'] ? 'activé' : 'désactivé' }}.</p>
                    @else
                        <p>Moins de 3 semaines d’historique : la précision sera mesurée quand il y aura assez de ventes.</p>
                    @endif
                </div>
            </li>
            @if (! $isSimulation)
                <li @class(['is-alert' => (bool) $stockout])>
                    <span class="step-n">7</span>
                    <div>
                        <p class="step-t">Stock et rupture</p>
                        @if ($stockout)
                            <p>Stock disponible : <b>{{ Fmt::q($available) }} u.</b> Au rythme prévu, <b>rupture le {{ CarbonImmutable::parse($stockout['date'])->locale('fr')->isoFormat('dddd D MMMM') }}</b>{{ $stockout['days'] === 0 ? ', dès aujourd’hui' : ', dans '.$stockout['days'].' jour(s)' }}.
                                <a href="{{ route('stock-movements.create', ['type' => 'transfer']) }}">Réapprovisionner</a></p>
                        @else
                            <p>Stock disponible : <b>{{ Fmt::q($available) }} u.</b> Pas de rupture prévue dans les 30 prochains jours.</p>
                        @endif
                    </div>
                </li>
            @endif
        </ol>
    </section>

    <section class="panel">
        <div class="panel-head"><div><h2>Détail jour par jour</h2></div></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Date</th><th>Période spéciale</th><th class="num">Prévision</th><th class="num">Fourchette probable</th></tr>
                </thead>
                <tbody>
                    @foreach ($f['days'] as $d)
                        <tr>
                            <td><b>{{ $d['weekday'] }}</b> {{ CarbonImmutable::parse($d['date'])->format('d/m/Y') }}</td>
                            <td>@if ($d['events'])<span class="tag tag-expiring">{{ implode(' + ', $d['events']) }} · ×{{ Fmt::n($d['multiplier'], 2) }}</span>@else<span class="muted">—</span>@endif</td>
                            <td class="num"><b>{{ Fmt::n($d['qty'], 1) }} u.</b></td>
                            <td class="num muted">{{ Fmt::n($d['low'], 0) }} à {{ Fmt::n($d['high'], 0) }} u.</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection

@push('styles')
<style>
    .shortcuts { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: -8px 0 20px; }
    .chip { display: inline-block; padding: 5px 12px; border-radius: 999px; border: 1px solid var(--rule); background: var(--panel); color: var(--ink); text-decoration: none; font-size: .86rem; font-weight: 600; }
    .chip:hover { border-color: var(--ink-soft); }
    .chip.is-on { background: var(--pine); border-color: var(--pine); color: #fff; }
    .chip.is-on .muted { color: #d6ebe3; }
    .chart-wrap { overflow-x: auto; }
    .chart { width: 100%; min-width: 640px; height: auto; display: block; }
    .chart text { font: 500 11px var(--font); fill: var(--ink-soft); }
    .chart .c-event { font-weight: 700; fill: #8a5a00; }
    .chart .c-grid { stroke: var(--rule); stroke-width: 1; }
    .chart .c-soft { fill: #9aaaa5; }
    .chart .c-bar { fill: #b9c8c3; }
    .chart .c-band { fill: var(--pine); fill-opacity: .14; }
    .chart .c-line { fill: none; stroke: var(--pine); stroke-width: 2.5; stroke-linejoin: round; }
    .chart .c-dot { fill: #fff; stroke: var(--pine); stroke-width: 2; }
    .chart .c-today { stroke: var(--ink); stroke-width: 1.5; stroke-dasharray: 4 4; }
    .chart .c-outlier { fill: var(--brick); font-size: 12px; }
    .sw { display: inline-block; width: 14px; height: 12px; margin-right: 6px; vertical-align: -1px; border-radius: 2px; }
    .sw-bar { background: #b9c8c3; }
    .sw-line { height: 3px; vertical-align: 3px; background: var(--pine); }
    .sw-band { background: rgba(15, 107, 88, .14); }
    .sw-event { background: var(--amber-wash); border: 1px solid #efd29a; }
    .steps { list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; }
    .steps > li { display: flex; gap: 14px; align-items: flex-start; padding: 12px 14px; border: 1px solid var(--rule); border-radius: 9px; }
    .steps > li.is-alert { border-color: #efc1ba; background: var(--brick-wash); }
    .steps p { margin: 2px 0 0; max-width: 95ch; }
    .step-n { flex: none; display: grid; place-items: center; width: 28px; height: 28px; border-radius: 50%; background: var(--pine); color: #fff; font-weight: 800; }
    .steps > li.is-alert .step-n { background: var(--brick); }
    .step-t { font-weight: 700; margin: 0 !important; }
    .events { margin: 4px 0 0; padding-left: 18px; }
    .week { display: flex; gap: 10px; margin: 8px 0 4px; flex-wrap: wrap; }
    .week-day { display: grid; justify-items: center; gap: 2px; font-size: .8rem; min-width: 48px; }
    .week-day b { font-variant-numeric: tabular-nums; }
    .week-bar { display: flex; align-items: flex-end; width: 26px; height: 60px; background: var(--slot); border-radius: 4px; overflow: hidden; }
    .week-bar i { display: block; width: 100%; background: var(--ink-soft); }
    .week-bar i.is-up { background: var(--pine); }
    .week-bar i.is-down { background: var(--amber); }
</style>
@endpush
