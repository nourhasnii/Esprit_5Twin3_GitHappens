@extends('layouts.stock')
@use('App\Support\Fmt')
@use('App\Support\BatchAttributes')
@use('App\Services\Optimization\OptimizationEngine')

@section('title', __('Trouver une destination'))
@section('eyebrow', __('Optimization workspace'))
@section('description', __('Choisissez un lot et son site d’origine pour comparer les destinations faisables.'))
@section('page-action')<a class="btn btn-ghost" href="{{ route('optimization.index') }}">{{ __('Retour aux recommandations') }}</a>@endsection

@section('content')
<div style="display:grid; grid-template-columns: minmax(0, 3fr) minmax(260px, 2fr); gap: 20px; align-items: start;" class="split">
    <form method="POST" action="{{ route('optimization.store') }}" class="panel" id="optim-form">
        @csrf
        <div class="form-grid" style="grid-template-columns: 1fr;">
            <div class="field">
                <label for="batch_id">{{ __('Lot à répartir') }}</label>
                <select id="batch_id" name="batch_id" required>
                    <option value="">{{ __('Choisir un lot') }}</option>
                    @foreach ($batches as $batch)
                        @php $d = BatchAttributes::daysToExpiry($batch); @endphp
                        <option value="{{ $batch->id }}" @selected(old('batch_id', $selectedBatch) == $batch->id)>
                            {{ BatchAttributes::code($batch) }}, {{ $batch->product?->name }}@if ($d !== null){{ __(', DLC dans :d j', ['d' => $d]) }} @endif
                        </option>
                    @endforeach
                </select>
                <p class="hint" id="location-hint" aria-live="polite">{{ __('Les lots rappelés ou périmés ne sont pas proposés.') }}</p>
                @error('batch_id')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="source_site_id">{{ __('Site d’origine') }}</label>
                <select id="source_site_id" name="source_site_id" required>
                    <option value="">{{ __('Choisir un site') }}</option>
                    @foreach ($sites as $site)
                        <option value="{{ $site->id }}" @selected(old('source_site_id', $selectedSource) == $site->id)>{{ $site->name }}</option>
                    @endforeach
                </select>
                @error('source_site_id')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="quantity">{{ __('Quantité à envoyer') }}</label>
                <input type="number" id="quantity" name="quantity" value="{{ old('quantity', $selectedQuantity) }}" min="0.01" step="any" required>
                @error('quantity')<p class="error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="form-foot">
            <button class="btn btn-primary" type="submit">{{ __('Calculer la recommandation') }}</button>
        </div>
    </form>

    <aside class="panel">
        <h2>{{ __('Comment le score est calculé') }}</h2>
        <p class="muted small" style="margin: 6px 0 14px;">{{ __('Les sites qui ne peuvent pas recevoir le lot (capacité insuffisante, arrivée après la DLC, position inconnue) sont écartés. Les autres reçoivent une note de 0 à 100 sur cinq critères, pondérés ainsi :') }}</p>
        @php $sum = array_sum($weights) ?: 1; @endphp
        <dl class="weights">
            @foreach (OptimizationEngine::CRITERIA as $key => $label)
                <div>
                    <dt>{{ __($label) }}</dt>
                    <dd><span class="bar"><span style="width: {{ round(($weights[$key] ?? 0) / $sum * 100) }}%"></span></span> {{ round(($weights[$key] ?? 0) / $sum * 100) }} %</dd>
                </div>
            @endforeach
        </dl>
        <p class="muted small" style="margin-top: 14px;">{{ __('Si la DLC est dans :urgent_expiry_days jours ou moins, la DLC et la proximité pèsent davantage.', ['urgent_expiry_days' => config('stock.optimization.urgent_expiry_days')]) }}</p>
    </aside>
</div>
@endsection

@push('styles')
<style>
    .weights { margin: 0; display: grid; gap: 10px; }
    .weights div { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; align-items: center; }
    .weights dt { font-size: 0.9rem; }
    .weights dd { margin: 0; display: flex; align-items: center; gap: 8px; font-variant-numeric: tabular-nums; font-size: 0.88rem; font-weight: 600; }
    .weights .bar { width: 90px; height: 8px; background: var(--slot); border-radius: 4px; overflow: hidden; display: inline-block; }
    .weights .bar span { display: block; height: 100%; background: var(--pine); }
    @media (max-width: 820px) { .split { grid-template-columns: 1fr !important; } }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const locations = @json($locations);
        const siteNames = @json($sites->pluck('name', 'id'));
        const batch = document.getElementById('batch_id');
        const source = document.getElementById('source_site_id');
        const quantity = document.getElementById('quantity');
        const hint = document.getElementById('location-hint');
        const fmt = new Intl.NumberFormat('fr-FR');

        const refresh = (prefill) => {
            const rows = locations[batch.value] || [];
            if (!batch.value) return;
            if (rows.length === 0) {
                hint.textContent = 'Ce lot n’est encore en stock sur aucun site : le transfert ne pourra pas être enregistré automatiquement.';
                return;
            }
            hint.textContent = 'En stock : ' + rows.map((r) => (siteNames[r.site_id] ?? 'Site ' + r.site_id) + ' (' + fmt.format(r.quantity) + ')').join(', ') + '.';
            if (prefill) {
                source.value = rows[0].site_id;
                quantity.value = rows[0].quantity;
            }
        };

        batch.addEventListener('change', () => refresh(true));
        source.addEventListener('change', () => {
            const row = (locations[batch.value] || []).find((r) => String(r.site_id) === source.value);
            if (row) quantity.value = row.quantity;
        });
        refresh(!source.value);
    })();
</script>
@endpush
