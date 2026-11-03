{{-- Police arabe (Cairo) chargée seulement quand l'interface est en arabe. --}}
@if (in_array(app()->getLocale(), config('app.rtl_locales', []), true))
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html[dir="rtl"] body, html[dir="rtl"] .font-inter, html[dir="rtl"] .font-fraunces,
        html[dir="rtl"] input, html[dir="rtl"] select, html[dir="rtl"] textarea, html[dir="rtl"] button {
            font-family: "Cairo", "Noto Kufi Arabic", Tahoma, sans-serif !important;
            letter-spacing: 0 !important;
        }
    </style>
@endif
