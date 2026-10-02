@use('App\Support\Fmt')
@use('App\Support\BatchAttributes')
@php
    $r = $recommendation;
    $batch = $r->batch;
    $number = sprintf('DON-%s-%04d-%d', $r->rescue_applied_at->format('Ymd'), $r->id, $donation['site_id']);
    $expiry = BatchAttributes::expiryDate($batch);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bon de don {{ $number }}</title>
    <style>
        :root { --ink: #12302b; --soft: #4d625d; --rule: #cfd9d5; --leaf: #3d7a1a; --leaf-wash: #e6f1dc; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f0; color: var(--ink); font: 400 14px/1.5 "Public Sans", "Segoe UI", system-ui, sans-serif; }
        .sheet { max-width: 800px; margin: 32px auto; background: #fff; padding: 44px 52px; border: 1px solid var(--rule); border-radius: 6px; }
        .head { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; border-bottom: 3px solid var(--leaf); padding-bottom: 18px; }
        .brand { font-weight: 800; font-size: 1.1rem; }
        .brand small { display: block; font-weight: 400; color: var(--soft); font-size: .85rem; }
        h1 { margin: 0 0 4px; font-size: 1.6rem; letter-spacing: -.01em; }
        .ref { text-align: right; color: var(--soft); font-size: .9rem; }
        .ref b { color: var(--ink); font-size: 1rem; font-variant-numeric: tabular-nums; }
        .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 26px 0; }
        .party { border: 1px solid var(--rule); border-radius: 6px; padding: 14px 16px; }
        .party span { display: block; font-size: .75rem; text-transform: uppercase; letter-spacing: .06em; color: var(--soft); margin-bottom: 4px; }
        .party b { font-size: 1.05rem; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 22px; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--rule); }
        th { font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; color: var(--soft); background: #f5f8f6; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        .impact { background: var(--leaf-wash); color: #274f10; border-radius: 6px; padding: 12px 16px; margin-bottom: 26px; }
        .conditions { color: var(--soft); font-size: .88rem; }
        .conditions ul { margin: 6px 0 0; padding-left: 18px; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; margin-top: 36px; }
        .signatures div { border-top: 1px solid var(--ink); padding-top: 6px; font-size: .88rem; min-height: 80px; }
        .toolbar { max-width: 800px; margin: 20px auto 0; display: flex; justify-content: space-between; gap: 12px; }
        .toolbar a, .toolbar button { font: 600 14px var(--font, inherit); padding: 9px 16px; border-radius: 6px; border: 1px solid var(--rule); background: #fff; color: var(--ink); text-decoration: none; cursor: pointer; }
        .toolbar button { background: var(--leaf); color: #fff; border-color: var(--leaf); }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; border: 0; padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('optimization.show', $r) }}">← Retour à la recommandation</a>
        <button type="button" onclick="window.print()">Imprimer le bon</button>
    </div>

    <main class="sheet">
        <div class="head">
            <div>
                <p class="brand">NutriTrace <small>Plan anti-gaspillage n° {{ $r->id }}</small></p>
                <h1>Bon de don alimentaire</h1>
            </div>
            <p class="ref">N° <b>{{ $number }}</b><br>Émis le {{ $r->rescue_applied_at->format('d/m/Y à H:i') }}</p>
        </div>

        <div class="parties">
            <div class="party">
                <span>Donateur</span>
                <b>{{ $r->sourceSite?->name }}</b><br>
                {{ $r->sourceSite?->address }}@if ($r->sourceSite?->address && $r->sourceSite?->city), @endif{{ $r->sourceSite?->city }}
            </div>
            <div class="party">
                <span>Bénéficiaire</span>
                <b>{{ $association?->name ?? $donation['site_name'] }}</b><br>
                {{ $association?->address }}@if ($association?->address && $association?->city), @endif{{ $association?->city ?? $donation['city'] }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Lot</th>
                    <th>DLC</th>
                    <th class="num">Quantité</th>
                    <th class="num">Poids estimé</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>{{ $r->product?->name }}</b></td>
                    <td>{{ BatchAttributes::code($batch) }}</td>
                    <td>{{ $expiry?->format('d/m/Y') ?? '—' }}</td>
                    <td class="num"><b>{{ Fmt::q($donation['quantity']) }} u.</b></td>
                    <td class="num">{{ Fmt::n($donation['quantity'] * ($plan['unit_kg'] ?? 1), 1) }} kg</td>
                </tr>
            </tbody>
        </table>

        <p class="impact">
            Ce don représente environ <b>{{ Fmt::q($donation['meals']) }} repas</b> et évite le gaspillage de
            <b>{{ Fmt::n($donation['quantity'] * ($plan['unit_kg'] ?? 1), 1) }} kg</b> de nourriture.
            Trajet : {{ Fmt::n($donation['distance_km'], 1) }} km, livraison {{ Fmt::n($donation['arrives_days_before_expiry'], 1) }} jour(s) avant la DLC.
        </p>

        <div class="conditions">
            <b>Conditions</b>
            <ul>
                <li>Les produits sont remis gratuitement, dans leur emballage d’origine et avant leur date limite de consommation.</li>
                <li>La chaîne du froid doit être respectée jusqu’à la distribution ; les produits ne peuvent pas être revendus.</li>
                <li>Le bénéficiaire vérifie l’état des produits à la réception et refuse tout produit abîmé.</li>
            </ul>
        </div>

        <div class="signatures">
            <div>Pour le donateur<br><span class="conditions">Nom, date et signature</span></div>
            <div>Pour le bénéficiaire<br><span class="conditions">Nom, date, signature et cachet</span></div>
        </div>
    </main>
</body>
</html>
