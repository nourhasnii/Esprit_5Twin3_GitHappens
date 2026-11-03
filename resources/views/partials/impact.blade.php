{{-- Bandeau d'impact cumulé des plans anti-gaspillage appliqués --}}
@php($impact = \App\Models\OptimizationRecommendation::impactTotals())
@if ($impact['plans'] > 0)
    <section class="impact" aria-label="{{ __('Impact anti-gaspillage') }}">
        <span class="impact-icon" aria-hidden="true">🌱</span>
        <p>
            <b>{{ __(':saved_kg kg', ['saved_kg' => \App\Support\Fmt::n($impact['saved_kg'], 1)]) }}</b> {{ __('de nourriture sauvés, dont') }} <b>{{ __(':donated_kg kg', ['donated_kg' => \App\Support\Fmt::n($impact['donated_kg'], 1)]) }}</b> {{ __('donnés, soit environ') }} <b>{{ \App\Support\Fmt::q($impact['meals']) }}</b> {{ __('repas offerts, et') }} <b>{{ __(':co2_avoided_kg kg CO₂e', ['co2_avoided_kg' => \App\Support\Fmt::n($impact['co2_avoided_kg'], 1)]) }}</b> {{ __('évités') }}
            <span class="muted">{{ __('· :plans plan(s) anti-gaspillage appliqué(s)', ['plans' => $impact['plans']]) }}</span>
        </p>
    </section>
@endif
