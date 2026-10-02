{{-- Bandeau d'impact cumulé des plans anti-gaspillage appliqués --}}
@php($impact = \App\Models\OptimizationRecommendation::impactTotals())
@if ($impact['plans'] > 0)
    <section class="impact" aria-label="Impact anti-gaspillage">
        <span class="impact-icon" aria-hidden="true">🌱</span>
        <p>
            <b>{{ \App\Support\Fmt::n($impact['saved_kg'], 1) }} kg</b> de nourriture sauvés,
            dont <b>{{ \App\Support\Fmt::n($impact['donated_kg'], 1) }} kg</b> donnés, soit environ <b>{{ \App\Support\Fmt::q($impact['meals']) }}</b> repas offerts,
            et <b>{{ \App\Support\Fmt::n($impact['co2_avoided_kg'], 1) }} kg CO₂e</b> évités
            <span class="muted">· {{ $impact['plans'] }} plan(s) anti-gaspillage appliqué(s)</span>
        </p>
    </section>
@endif
