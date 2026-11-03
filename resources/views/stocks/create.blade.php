@extends('layouts.stock')
@use('App\Support\BatchAttributes')

@section('title', __('Nouvelle ligne de stock'))
@section('eyebrow', __('Inventory workspace'))
@section('description', __('Enregistrez le stock initial; il sera ajouté à l’historique comme un mouvement d’entrée.'))
@section('page-action')<a class="btn btn-ghost" href="{{ route('stocks.index') }}">{{ __('Retour aux stocks') }}</a>@endsection

@section('content')
<form method="POST" action="{{ route('stocks.store') }}" class="panel">
    @csrf
    <div class="form-grid">
        <div class="field">
            <label for="site_id">{{ __('Site') }}</label>
            <select id="site_id" name="site_id" required>
                <option value="">{{ __('Choisir un site') }}</option>
                @foreach ($sites as $site)
                    <option value="{{ $site->id }}" @selected(old('site_id', $selectedSite) == $site->id)>{{ $site->name }}</option>
                @endforeach
            </select>
            @error('site_id')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="batch_id">{{ __('Lot') }}</label>
            <select id="batch_id" name="batch_id">
                <option value="">{{ __('Sans lot (suivi par produit)') }}</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}" data-product="{{ $batch->product_id }}" @selected(old('batch_id') == $batch->id)>
                        {{ BatchAttributes::code($batch) }}, {{ $batch->product?->name }}
                        @if ($d = BatchAttributes::expiryDate($batch)) {{ __('(DLC :y)', ['y' => $d->format('d/m/Y')]) }} @endif
                    </option>
                @endforeach
            </select>
            <p class="hint">{{ __('Si vous choisissez un lot, son produit est utilisé automatiquement.') }}</p>
            @error('batch_id')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="product_id">{{ __('Produit') }}</label>
            <select id="product_id" name="product_id">
                <option value="">{{ __('Choisir un produit') }}</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
            @error('product_id')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="quantity">{{ __('Quantité initiale') }}</label>
            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 0) }}" min="0" step="any" required>
            @error('quantity')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="min_threshold">{{ __('Seuil de rupture') }}</label>
            <input type="number" id="min_threshold" name="min_threshold" value="{{ old('min_threshold', 0) }}" min="0" step="any" required>
            <p class="hint">{{ __('Une alerte est envoyée quand le stock disponible du produit sur ce site passe sous ce niveau. 0 désactive l’alerte.') }}</p>
            @error('min_threshold')<p class="error">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">{{ __('Créer la ligne de stock') }}</button>
        <a class="btn btn-ghost" href="{{ route('stocks.index') }}">{{ __('Annuler') }}</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (() => {
        const batch = document.getElementById('batch_id');
        const product = document.getElementById('product_id');
        batch.addEventListener('change', () => {
            const id = batch.selectedOptions[0]?.dataset.product;
            if (id) product.value = id;
        });
    })();
</script>
@endpush
