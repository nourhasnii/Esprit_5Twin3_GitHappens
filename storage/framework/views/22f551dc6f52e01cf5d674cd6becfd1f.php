<?php use \App\Support\Fmt; ?>
<?php use \App\Services\Forecast\DemandForecaster; ?>
<?php use \Carbon\CarbonImmutable; ?>

<?php $__env->startSection('eyebrow', 'Forecast workspace'); ?>
<?php $__env->startSection('title', 'Prévisions de la demande'); ?>
<?php $__env->startSection('description', 'Prévisions basées sur les ventes historiques et le calendrier tunisien; elles alimentent l’optimisation sans afficher de données simulées comme des ventes réelles.'); ?>

<?php $__env->startSection('content'); ?>
<form class="filters" method="GET" action="<?php echo e(route('forecast.index')); ?>">
    <div class="field">
        <label for="f-site">Site</label>
        <select id="f-site" name="site_id">
            <?php $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s->id); ?>" <?php if($site?->id === $s->id): echo 'selected'; endif; ?>><?php echo e($s->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="field">
        <label for="f-product">Produit</label>
        <select id="f-product" name="product_id">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($p->id); ?>" <?php if($product?->id === $p->id): echo 'selected'; endif; ?>><?php echo e($p->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="field">
        <label for="f-from">À partir du</label>
        <input type="date" id="f-from" name="from" value="<?php echo e($from->toDateString()); ?>">
    </div>
    <div class="field">
        <label for="f-days">Horizon</label>
        <select id="f-days" name="days">
            <?php $__currentLoopData = [7, 14, 30]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($d); ?>" <?php if($days === $d): echo 'selected'; endif; ?>><?php echo e($d); ?> jours</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <button class="btn btn-primary" type="submit">Prévoir</button>
</form>

<?php if($shortcuts): ?>
    <p class="shortcuts">
        <span class="muted small">Simuler une période :</span>
        <a class="<?php echo \Illuminate\Support\Arr::toCssClasses(['chip', 'is-on' => ! $isSimulation]); ?>" href="<?php echo e(route('forecast.index', ['site_id' => $site?->id, 'product_id' => $product?->id, 'days' => $days])); ?>">Aujourd’hui</a>
        <?php $__currentLoopData = $shortcuts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a class="<?php echo \Illuminate\Support\Arr::toCssClasses(['chip', 'is-on' => $from->toDateString() === $sc['from']]); ?>" href="<?php echo e(route('forecast.index', ['site_id' => $site?->id, 'product_id' => $product?->id, 'days' => $days, 'from' => $sc['from']])); ?>"><?php echo e($sc['label']); ?> <span class="muted">· <?php echo e($sc['date']->format('d/m/Y')); ?></span></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </p>
<?php endif; ?>

<?php if(! $forecast || ! $forecast['has_history']): ?>
    <section class="panel">
        <p><b>Pas encore de ventes enregistrées</b> pour <?php echo e($product?->name ?? 'ce produit'); ?> à <?php echo e($site?->name ?? 'ce site'); ?> sur les <?php echo e(config('stock.forecast.history_days')); ?> derniers jours : la prévision vaut 0.</p>
        <p class="muted small">Les dons, la casse et les destructions ne comptent pas comme des ventes. Choisissez un magasin qui vend ce produit.</p>
    </section>
<?php else: ?>
    <?php
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
    ?>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2><?php echo e($product->name); ?> · <?php echo e($site->name); ?></h2>
                <p class="muted small">
                    <?php if($isSimulation): ?>
                        Simulation à partir du <?php echo e($from->format('d/m/Y')); ?> : prévision de <?php echo e(Fmt::n($f['total'], 0)); ?> u. sur <?php echo e($days); ?> jours, soit <?php echo e(Fmt::n($f['average'], 1)); ?> u./jour en moyenne.
                    <?php else: ?>
                        Prévision de <?php echo e(Fmt::n($f['total'], 0)); ?> u. sur les <?php echo e($days); ?> prochains jours, soit <?php echo e(Fmt::n($f['average'], 1)); ?> u./jour en moyenne (moyenne simple des ventes passées : <?php echo e(Fmt::n($f['simple_average'], 1)); ?>).
                    <?php endif; ?>
                </p>
            </div>
            <?php if($stockout): ?>
                <span class="tag tag-low">Rupture prévue le <?php echo e(CarbonImmutable::parse($stockout['date'])->locale('fr')->isoFormat('dddd DD/MM')); ?></span>
            <?php endif; ?>
        </div>

        <div class="chart-wrap">
            <svg viewBox="0 0 <?php echo e($W); ?> <?php echo e($H); ?>" class="chart" role="img" aria-label="Ventes des 21 derniers jours et prévision">
                <?php $__currentLoopData = $bands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <rect x="<?php echo e($L + $b['from'] * $slot); ?>" y="<?php echo e($T); ?>" width="<?php echo e(($b['to'] - $b['from'] + 1) * $slot); ?>" height="<?php echo e($H - $T - $B); ?>" fill="var(--amber-wash)"/>
                    <text x="<?php echo e($L + $b['from'] * $slot + 4); ?>" y="<?php echo e($T - 8); ?>" class="c-event"><?php echo e(\Illuminate\Support\Str::limit($b['key'], max(8, (int) (($b['to'] - $b['from'] + 1) * $slot / 6.5)))); ?></text>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php for($v = 0; $v <= $top; $v += $step): ?>
                    <line x1="<?php echo e($L); ?>" x2="<?php echo e($W - $R); ?>" y1="<?php echo e($y($v)); ?>" y2="<?php echo e($y($v)); ?>" class="c-grid"/>
                    <text x="<?php echo e($L - 8); ?>" y="<?php echo e($y($v) + 4); ?>" class="c-axis" text-anchor="end"><?php echo e(Fmt::q($v)); ?></text>
                <?php endfor; ?>

                <?php $__currentLoopData = $points; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($p['kind'] === 'h'): ?>
                        <rect x="<?php echo e($L + $i * $slot + $slot * 0.18); ?>" y="<?php echo e($y($p['qty'])); ?>" width="<?php echo e($slot * 0.64); ?>" height="<?php echo e(max(0, $y(0) - $y($p['qty']))); ?>" rx="2" class="c-bar">
                            <title><?php echo e(CarbonImmutable::parse($p['date'])->format('d/m')); ?> : <?php echo e(Fmt::q($p['raw'])); ?> u. vendues</title>
                        </rect>
                        <?php if($p['raw'] > $p['qty'] + 0.01): ?>
                            <text x="<?php echo e($x($i)); ?>" y="<?php echo e($y($p['qty']) - 6); ?>" text-anchor="middle" class="c-outlier">▲<title>Vente exceptionnelle : <?php echo e(Fmt::q($p['raw'])); ?> u., ramenée à <?php echo e(Fmt::q($p['qty'])); ?> u.</title></text>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <polygon points="<?php echo e($band); ?>" class="c-band"/>
                <polyline points="<?php echo e($line); ?>" class="c-line"/>
                <?php $__currentLoopData = $points; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($p['kind'] === 'f'): ?>
                        <circle cx="<?php echo e($x($i)); ?>" cy="<?php echo e($y($p['qty'])); ?>" r="3.5" class="c-dot">
                            <title><?php echo e($p['weekday']); ?> <?php echo e(CarbonImmutable::parse($p['date'])->format('d/m')); ?> : <?php echo e(Fmt::n($p['qty'], 1)); ?> u. prévues (<?php echo e(Fmt::n($p['low'], 0)); ?> à <?php echo e(Fmt::n($p['high'], 0)); ?>)</title>
                        </circle>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <line x1="<?php echo e($L + $firstForecast * $slot); ?>" x2="<?php echo e($L + $firstForecast * $slot); ?>" y1="<?php echo e($T - 4); ?>" y2="<?php echo e($H - $B); ?>" class="c-today"/>
                <text x="<?php echo e($L + $firstForecast * $slot + 5); ?>" y="<?php echo e($H - $B - 6); ?>" class="c-axis"><?php echo e($isSimulation ? '⋯ simulation' : 'aujourd’hui'); ?></text>

                <?php $__currentLoopData = $points; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($i % $labelEvery === 0): ?>
                        <text x="<?php echo e($x($i)); ?>" y="<?php echo e($H - $B + 16); ?>" text-anchor="middle" class="c-axis"><?php echo e(CarbonImmutable::parse($p['date'])->format('d/m')); ?></text>
                        <text x="<?php echo e($x($i)); ?>" y="<?php echo e($H - $B + 30); ?>" text-anchor="middle" class="c-axis c-soft"><?php echo e(DemandForecaster::WEEKDAYS[CarbonImmutable::parse($p['date'])->dayOfWeekIso]); ?></text>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </svg>
        </div>
        <div class="map-legend">
            <span><i class="sw sw-bar"></i>Ventes réelles (21 derniers jours)</span>
            <span><i class="sw sw-line"></i>Prévision</span>
            <span><i class="sw sw-band"></i>Fourchette probable (80 %)</span>
            <span><i class="sw sw-event"></i>Période spéciale du calendrier</span>
            <?php if($f['outliers']): ?><span><b style="color:var(--brick)">▲</b> Vente exceptionnelle corrigée</span><?php endif; ?>
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
                    <p><?php echo e($f['history_days']); ?> jour(s) de ventes sur les <?php echo e(config('stock.forecast.history_days')); ?> derniers jours.
                        <?php if($f['missing_days'] > 0): ?> <?php echo e($f['missing_days']); ?> jour(s) sans aucune saisie dans le magasin sont ignorés (magasin fermé ou saisie oubliée), au lieu de compter pour zéro. <?php endif; ?>
                        Les dons, la casse et les destructions ne sont pas comptés comme des ventes.</p>
                </div>
            </li>
            <li>
                <span class="step-n">2</span>
                <div>
                    <p class="step-t">Ventes exceptionnelles</p>
                    <?php $__empty_1 = true; $__currentLoopData = $f['outliers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <p>Le <?php echo e(CarbonImmutable::parse($o['date'])->format('d/m')); ?>, <?php echo e(Fmt::q($o['original'])); ?> u. vendues au lieu d’environ <?php echo e(Fmt::n($f['simple_average'], 0)); ?> habituellement : valeur ramenée à <?php echo e(Fmt::n($o['capped'], 0)); ?> u. pour ne pas fausser la prévision (erreur de saisie ou commande isolée ?).</p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p>Aucune vente anormale détectée.</p>
                    <?php endif; ?>
                </div>
            </li>
            <li>
                <span class="step-n">3</span>
                <div>
                    <p class="step-t">Rythme de la semaine</p>
                    <?php if(($acc['weekly'] ?? true) && collect($f['weekday_factors'])->contains(fn ($v) => abs($v - 1) > 0.03)): ?>
                        <div class="week">
                            <?php $__currentLoopData = $f['weekday_factors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wd => $factor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="week-day">
                                    <span class="week-bar"><i style="height: <?php echo e(max(8, min(100, 50 * $factor))); ?>%" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['is-up' => $factor > 1.03, 'is-down' => $factor < 0.97]); ?>"></i></span>
                                    <b><?php echo e($factor >= 1 ? '+' : '−'); ?><?php echo e(Fmt::n(abs($factor - 1) * 100)); ?> %</b>
                                    <span><?php echo e(DemandForecaster::WEEKDAYS[$wd]); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <p class="muted small">Appris sur l’historique, sans règle fixée à l’avance.</p>
                    <?php else: ?>
                        <p>Aucun rythme hebdomadaire fiable dans ces ventes : l’auto-calibrage a désactivé cet effet, qui n’aurait ajouté que du bruit.</p>
                    <?php endif; ?>
                </div>
            </li>
            <li>
                <span class="step-n">4</span>
                <div>
                    <p class="step-t">Niveau et tendance</p>
                    <p>Niveau actuel d’environ <b><?php echo e(Fmt::n($f['level'], 1)); ?> u./jour</b>, tendance de <b><?php echo e($f['trend'] >= 0 ? '+' : '−'); ?><?php echo e(Fmt::n(abs($f['trend']), 2)); ?> u./jour</b>, amortie avec le temps pour ne pas s’emballer (lissage exponentiel de Holt).</p>
                </div>
            </li>
            <li>
                <span class="step-n">5</span>
                <div>
                    <p class="step-t">Calendrier tunisien</p>
                    <?php if($eventsAhead->isEmpty()): ?>
                        <p>Aucune période spéciale sur les <?php echo e($days); ?> jours prévus.<?php if($f['category']): ?> Produit reconnu comme « <?php echo e($f['category']); ?> ». <?php endif; ?></p>
                    <?php else: ?>
                        <p>Périodes appliquées pour ce produit <?php echo e($f['category'] ? '(famille « '.$f['category'].' »)' : ''); ?><?php echo e($f['coastal'] ? ' dans une ville balnéaire' : ''); ?> :</p>
                        <ul class="events">
                            <?php $__currentLoopData = $eventsAhead; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ($m = $multipliers[$label]['multipliers'] ?? []); ?>
                                <?php ($coef = (float) ($f['category'] && isset($m[$f['category']]) ? $m[$f['category']] : ($m['*'] ?? 1))); ?>
                                <li><b><?php echo e($label); ?></b> : ventes × <?php echo e(Fmt::n($coef, 2)); ?> (<?php echo e($coef >= 1 ? '+' : '−'); ?><?php echo e(Fmt::n(abs($coef - 1) * 100)); ?> %)</li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php endif; ?>
                    <p class="muted small">Hypothèses métier modifiables dans <code>config/stock.php</code>. Les dates des fêtes religieuses sont estimées (calendrier lunaire).</p>
                </div>
            </li>
            <li>
                <span class="step-n">6</span>
                <div>
                    <p class="step-t">Précision mesurée</p>
                    <?php if($acc): ?>
                        <?php ($ratio = $acc['error'] > 0 ? $acc['baseline_error'] / $acc['error'] : null); ?>
                        <p>L’IA a rejoué les <?php echo e($acc['test_days']); ?> derniers jours comme si elle ne les connaissait pas. Elle se trompe en moyenne de <b><?php echo e(Fmt::n($acc['error'], 1)); ?> %</b>, contre <b><?php echo e(Fmt::n($acc['baseline_error'], 1)); ?> %</b> pour une moyenne simple.
                            <?php if($ratio && $ratio >= 1.15): ?>
                                Elle est donc <b><?php echo e(Fmt::n($ratio, 1)); ?> fois plus précise</b>.
                            <?php elseif($ratio && $ratio >= 0.85): ?>
                                Les deux méthodes sont comparables sur ces ventes, qui ne suivent pas de rythme marqué.
                            <?php else: ?>
                                Sur ces ventes très irrégulières, la moyenne simple fait un peu mieux : la prévision reste prudente.
                            <?php endif; ?>
                        </p>
                        <p class="muted small">Auto-calibrage : <?php echo e($acc['tried']); ?> réglages essayés ; retenu : lissage <?php echo e(Fmt::n($acc['alpha'], 2)); ?>, tendance <?php echo e($acc['beta'] > 0 ? 'activée' : 'désactivée'); ?>, rythme de la semaine <?php echo e($acc['weekly'] ? 'activé' : 'désactivé'); ?>.</p>
                    <?php else: ?>
                        <p>Moins de 3 semaines d’historique : la précision sera mesurée quand il y aura assez de ventes.</p>
                    <?php endif; ?>
                </div>
            </li>
            <?php if(! $isSimulation): ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['is-alert' => (bool) $stockout]); ?>">
                    <span class="step-n">7</span>
                    <div>
                        <p class="step-t">Stock et rupture</p>
                        <?php if($stockout): ?>
                            <p>Stock disponible : <b><?php echo e(Fmt::q($available)); ?> u.</b> Au rythme prévu, <b>rupture le <?php echo e(CarbonImmutable::parse($stockout['date'])->locale('fr')->isoFormat('dddd D MMMM')); ?></b><?php echo e($stockout['days'] === 0 ? ', dès aujourd’hui' : ', dans '.$stockout['days'].' jour(s)'); ?>.
                                <a href="<?php echo e(route('stock-movements.create', ['type' => 'transfer'])); ?>">Réapprovisionner</a></p>
                        <?php else: ?>
                            <p>Stock disponible : <b><?php echo e(Fmt::q($available)); ?> u.</b> Pas de rupture prévue dans les 30 prochains jours.</p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endif; ?>
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
                    <?php $__currentLoopData = $f['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><b><?php echo e($d['weekday']); ?></b> <?php echo e(CarbonImmutable::parse($d['date'])->format('d/m/Y')); ?></td>
                            <td><?php if($d['events']): ?><span class="tag tag-expiring"><?php echo e(implode(' + ', $d['events'])); ?> · ×<?php echo e(Fmt::n($d['multiplier'], 2)); ?></span><?php else: ?><span class="muted">—</span><?php endif; ?></td>
                            <td class="num"><b><?php echo e(Fmt::n($d['qty'], 1)); ?> u.</b></td>
                            <td class="num muted"><?php echo e(Fmt::n($d['low'], 0)); ?> à <?php echo e(Fmt::n($d['high'], 0)); ?> u.</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\forecast\index.blade.php ENDPATH**/ ?>