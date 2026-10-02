<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Stocks') | NutriTrace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --mist: #eef2f0;
            --panel: #ffffff;
            --ink: #12302b;
            --ink-soft: #4d625d;
            --rule: #d6dfdb;
            --pine: #0f6b58;
            --pine-deep: #0a4d40;
            --pine-wash: #e1efe9;
            --amber: #9a5b00;
            --amber-wash: #fcefd6;
            --brick: #a3261d;
            --brick-wash: #fbe3df;
            --sky: #245d8a;
            --sky-wash: #e2edf6;
            --slot: #dde5e2;
            --leaf: #3d7a1a;
            --leaf-wash: #e6f1dc;
            --rose: #a8325e;
            --rose-wash: #f8e3eb;
            --font: "Public Sans", "Segoe UI", system-ui, -apple-system, sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body { margin: 0; background: var(--mist); color: var(--ink); font: 400 15px/1.55 var(--font); }
        a { color: var(--pine); text-underline-offset: 2px; }
        a:hover { color: var(--pine-deep); }
        :focus-visible { outline: 3px solid #5fb49c; outline-offset: 2px; border-radius: 4px; }
        h1, h2, h3 { margin: 0; line-height: 1.2; letter-spacing: -0.015em; }
        h1 { font-size: 1.9rem; font-weight: 800; }
        h2 { font-size: 1.15rem; font-weight: 700; }
        h3 { font-size: 0.98rem; font-weight: 700; }
        p { margin: 0; }
        .num, td.num, th.num { font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
        .muted { color: var(--ink-soft); }
        .small { font-size: 0.85rem; }

        /* Barre supérieure */
        .topbar { background: var(--ink); color: #e9f1ee; }
        .topbar-inner { max-width: 1180px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; gap: 32px; min-height: 60px; flex-wrap: wrap; }
        .brand { font-weight: 800; font-size: 1.05rem; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .brand:hover { color: #fff; }
        .brand-mark { width: 22px; height: 22px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; }
        .brand-mark span { background: #6cc3a8; border-radius: 1px; }
        .brand-mark span:nth-child(3n) { background: #2f8f76; }
        .tabs { display: flex; gap: 4px; flex-wrap: wrap; }
        .tabs a { color: #b9cdc6; text-decoration: none; padding: 19px 12px 17px; font-weight: 600; font-size: 0.93rem; border-bottom: 3px solid transparent; }
        .tabs a:hover { color: #fff; }
        .tabs a[aria-current="page"] { color: #fff; border-bottom-color: #6cc3a8; }

        main { max-width: 1180px; margin: 0 auto; padding: 32px 24px 64px; }

        .page-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
        .page-head .lede { margin-top: 6px; color: var(--ink-soft); max-width: 62ch; }
        .back { display: inline-block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 600; text-decoration: none; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }

        /* Boutons */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 16px; border-radius: 7px; font: 600 0.92rem var(--font); border: 1px solid transparent; cursor: pointer; text-decoration: none; }
        .btn-primary { background: var(--pine); color: #fff; }
        .btn-primary:hover { background: var(--pine-deep); color: #fff; }
        .btn-ghost { background: var(--panel); color: var(--ink); border-color: var(--rule); }
        .btn-ghost:hover { border-color: var(--ink-soft); color: var(--ink); }
        .btn-danger { background: transparent; color: var(--brick); border-color: #e6b7b1; }
        .btn-danger:hover { background: var(--brick-wash); }
        .linklike { background: none; border: 0; padding: 0; font: inherit; color: var(--brick); cursor: pointer; text-decoration: underline; text-underline-offset: 2px; }

        /* Panneaux */
        .panel { background: var(--panel); border: 1px solid var(--rule); border-radius: 10px; padding: 20px 22px; margin-bottom: 20px; }
        .panel-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 14px; }
        .panel-head p { margin-top: 3px; }
        .panel > .table-wrap { margin: 0 -22px -20px; border-top: 1px solid var(--rule); }

        /* Indicateurs */
        .figures { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 1px; background: var(--rule); border: 1px solid var(--rule); border-radius: 10px; overflow: hidden; margin-bottom: 20px; }
        .figure { background: var(--panel); padding: 16px 20px; }
        .figure strong { display: block; font-size: 1.7rem; font-weight: 800; font-variant-numeric: tabular-nums; letter-spacing: -0.02em; }
        .figure span { color: var(--ink-soft); font-size: 0.88rem; }
        .figure.is-alert strong { color: var(--brick); }
        .figure.is-warn strong { color: var(--amber); }

        /* Tableaux */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 0.8rem; font-weight: 600; color: var(--ink-soft); padding: 10px 14px; border-bottom: 1px solid var(--rule); background: #f7faf9; white-space: nowrap; }
        td { padding: 11px 14px; border-bottom: 1px solid #e8eeeb; vertical-align: top; }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:hover td { background: #fafcfb; }
        tr.is-off td { color: var(--ink-soft); background: #fafbfb; }
        .row-actions { white-space: nowrap; text-align: right; }
        .row-actions a, .row-actions form { margin-left: 12px; display: inline; font-size: 0.9rem; }
        .code { font-weight: 600; letter-spacing: 0.01em; }

        /* Étiquettes d'état */
        .tag { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; background: var(--slot); color: var(--ink); }
        .tag-ok, .tag-accepted, .tag-in { background: var(--pine-wash); color: var(--pine-deep); }
        .tag-low, .tag-expired, .tag-recalled, .tag-rejected, .tag-out { background: var(--brick-wash); color: var(--brick); }
        .tag-expiring, .tag-pending, .tag-adjustment { background: var(--amber-wash); color: var(--amber); }
        .tag-transfer, .tag-modified { background: var(--sky-wash); color: var(--sky); }
        .tag-rescued { background: var(--leaf-wash); color: var(--leaf); }
        .impact { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; background: var(--leaf-wash); border: 1px solid #c9e0b4; color: #274f10; border-radius: 9px; padding: 12px 16px; margin-bottom: 20px; }
        .impact-icon { font-size: 1.5rem; line-height: 1; }
        .impact p { margin: 0; }
        .impact b { font-variant-numeric: tabular-nums; }
        .impact .muted { color: #4f6d3c; }

        /* Jauge de capacité en emplacements de rack */
        .rack { --fill: var(--pine); position: relative; height: 16px; min-width: 160px; border-radius: 3px;
                background: repeating-linear-gradient(90deg, var(--slot) 0 9px, transparent 9px 12px); }
        .rack > span { position: absolute; inset: 0 auto 0 0; border-radius: 3px;
                       background: repeating-linear-gradient(90deg, var(--fill) 0 9px, transparent 9px 12px); }
        .rack.is-mid { --fill: #c48a1a; }
        .rack.is-high { --fill: var(--brick); }
        .capacity { display: grid; gap: 5px; min-width: 220px; }
        .capacity-legend { display: flex; justify-content: space-between; gap: 12px; font-size: 0.84rem; color: var(--ink-soft); }
        .capacity-legend b { color: var(--ink); font-variant-numeric: tabular-nums; }

        /* Formulaires */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px 20px; }
        .field { display: grid; gap: 6px; align-content: start; }
        .field.wide { grid-column: 1 / -1; }
        .field[hidden] { display: none; }
        label, .label { font-weight: 600; font-size: 0.9rem; }
        input[type=text], input[type=number], input[type=date], input[type=datetime-local], select, textarea {
            width: 100%; padding: 9px 11px; border: 1px solid #c3cfca; border-radius: 7px; font: 400 0.95rem var(--font); color: var(--ink); background: #fff;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--pine); outline: 2px solid #b9e0d3; outline-offset: 0; }
        textarea { min-height: 88px; resize: vertical; }
        .hint { font-size: 0.83rem; color: var(--ink-soft); }
        .error { font-size: 0.84rem; color: var(--brick); font-weight: 600; }
        .check { display: flex; gap: 10px; align-items: center; font-weight: 500; }
        .check[hidden] { display: none; }
        .check input { width: 18px; height: 18px; accent-color: var(--pine); }
        .form-foot { display: flex; gap: 10px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--rule); flex-wrap: wrap; }

        .filters { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 20px; }
        .filters .field { min-width: 170px; flex: 1; }
        .filters .field label { font-size: 0.8rem; color: var(--ink-soft); }

        /* Choix en tuiles (type de mouvement, décision) */
        .choices { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; border: 0; padding: 0; margin: 0 0 22px; }
        .choices legend { font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; padding: 0; }
        .choice { position: relative; display: block; border: 1px solid #c3cfca; border-radius: 9px; padding: 12px 14px 12px 40px; cursor: pointer; background: #fff; font-weight: 400; }
        .choice input { position: absolute; left: 14px; top: 15px; accent-color: var(--pine); width: 16px; height: 16px; }
        .choice b { display: block; }
        .choice small { color: var(--ink-soft); font-size: 0.84rem; }
        .choice:has(input:checked) { border-color: var(--pine); background: var(--pine-wash); box-shadow: inset 0 0 0 1px var(--pine); }
        .choice:has(input:disabled) { opacity: .5; cursor: not-allowed; }

        /* Messages */
        .notice { border-radius: 9px; padding: 13px 16px; margin-bottom: 20px; border: 1px solid; }
        .notice-success { background: var(--pine-wash); border-color: #b5d9cb; color: var(--pine-deep); }
        .notice-error { background: var(--brick-wash); border-color: #efc1ba; color: #7d1c15; }
        .notice-warning { background: var(--amber-wash); border-color: #efd29a; color: #5f3900; }
        .notice-warning .btn { margin-top: 10px; }
        .notice ul { margin: 6px 0 0; padding-left: 18px; }
        .notice-title { font-weight: 700; }

        .empty { padding: 28px 22px; text-align: center; color: var(--ink-soft); }
        .empty .btn { margin-top: 12px; }
        .pager { margin-top: 16px; }
        .pagination { display: flex; gap: 4px; list-style: none; padding: 0; margin: 0; flex-wrap: wrap; }
        .pagination li > a, .pagination li > span { display: block; min-width: 36px; text-align: center; padding: 6px 10px; border: 1px solid var(--rule); border-radius: 6px; background: #fff; text-decoration: none; font-variant-numeric: tabular-nums; }
        .pagination .active > span { background: var(--pine); border-color: var(--pine); color: #fff; }
        .pagination .disabled > span { color: #9aaaa5; }

        @media (max-width: 720px) {
            main { padding: 22px 16px 48px; }
            .topbar-inner { padding: 10px 16px; gap: 10px; }
            .tabs a { padding: 8px 10px; }
            h1 { font-size: 1.5rem; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; animation: none !important; } }
    </style>
    @stack('styles')
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="{{ route('stocks.index') }}">
            <span class="brand-mark" aria-hidden="true">@for ($i = 0; $i < 9; $i++)<span></span>@endfor</span>
            NutriTrace Stocks
        </a>
        <nav class="tabs" aria-label="Module stocks">
            <a href="{{ route('stocks.index') }}" @if (request()->routeIs('stocks.*')) aria-current="page" @endif>Stocks</a>
            <a href="{{ route('stock-movements.index') }}" @if (request()->routeIs('stock-movements.*')) aria-current="page" @endif>Mouvements</a>
            <a href="{{ route('forecast.index') }}" @if (request()->routeIs('forecast.*')) aria-current="page" @endif>Prévisions</a>
            <a href="{{ route('optimization.index') }}" @if (request()->routeIs('optimization.*')) aria-current="page" @endif>Optimisation</a>
            <a href="{{ route('sites.index') }}" @if (request()->routeIs('sites.*')) aria-current="page" @endif>Sites</a>
        </nav>
    </div>
</header>

<main>
    @if (session('success'))
        <div class="notice notice-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="notice notice-error" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="notice notice-error" role="alert">
            <p class="notice-title">Corrigez les points suivants :</p>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</main>

@stack('scripts')
</body>
</html>
