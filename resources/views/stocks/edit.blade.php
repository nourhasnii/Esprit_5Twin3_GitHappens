@extends('layouts.stock')
@use('App\Support\Fmt')

@section('title', __('Seuil de rupture'))
@section('eyebrow', __('Inventory workspace'))
@section('description', __('Le seuil s’applique à ce produit sur ce site; les quantités ne changent que par des mouvements.'))
@section('page-action')<a class="btn btn-ghost" href="{{ route('stocks.index', ['site_id' => $stock->site_id]) }}">{{ __('Retour aux stocks du site') }}</a>@endsection

@section('content')
<form method="POST" action="{{ route('stocks.update', $stock) }}" class="panel">
    @csrf @method('PUT')
    <div class="form-grid">
        <div class="field">
            <span class="label">{{ __('Disponible actuellement') }}</span>
            <p style="font-size:1.5rem;font-weight:800" class="num" >{{ Fmt::q($productAvailable) }}</p>
        </div>
        <div class="field">
            <label for="min_threshold">{{ __('Seuil de rupture') }}</label>
            <input type="number" id="min_threshold" name="min_threshold" value="{{ old('min_threshold', $stock->min_threshold) }}" min="0" step="any" required autofocus>
            @error('min_threshold')<p class="error">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">{{ __('Enregistrer le seuil') }}</button>
        <a class="btn btn-ghost" href="{{ route('stocks.index', ['site_id' => $stock->site_id]) }}">{{ __('Annuler') }}</a>
    </div>
</form>

@if ($stock->quantity <= 0)
    <form method="POST" action="{{ route('stocks.destroy', $stock) }}" onsubmit="return confirm('Supprimer cette ligne vide ?')">
        @csrf @method('DELETE')
        <button class="btn btn-danger" type="submit">{{ __('Supprimer cette ligne vide') }}</button>
    </form>
@endif
@endsection
