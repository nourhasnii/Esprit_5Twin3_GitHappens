@extends('layouts.stock')
@use('App\Support\Fmt')

@section('title', 'Seuil de rupture')

@section('content')
<div class="page-head">
    <div>
        <a class="back" href="{{ route('stocks.index', ['site_id' => $stock->site_id]) }}">Retour aux stocks du site</a>
        <h1>Seuil de {{ $stock->product?->name }}</h1>
        <p class="lede">Sur {{ $stock->site?->name }}. Le seuil s’applique au produit sur ce site, tous lots confondus. Les quantités, elles, ne changent que par des mouvements.</p>
    </div>
</div>

<form method="POST" action="{{ route('stocks.update', $stock) }}" class="panel">
    @csrf @method('PUT')
    <div class="form-grid">
        <div class="field">
            <span class="label">Disponible actuellement</span>
            <p style="font-size:1.5rem;font-weight:800" class="num" >{{ Fmt::q($productAvailable) }}</p>
        </div>
        <div class="field">
            <label for="min_threshold">Seuil de rupture</label>
            <input type="number" id="min_threshold" name="min_threshold" value="{{ old('min_threshold', $stock->min_threshold) }}" min="0" step="any" required autofocus>
            @error('min_threshold')<p class="error">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">Enregistrer le seuil</button>
        <a class="btn btn-ghost" href="{{ route('stocks.index', ['site_id' => $stock->site_id]) }}">Annuler</a>
    </div>
</form>

@if ($stock->quantity <= 0)
    <form method="POST" action="{{ route('stocks.destroy', $stock) }}" onsubmit="return confirm('Supprimer cette ligne vide ?')">
        @csrf @method('DELETE')
        <button class="btn btn-danger" type="submit">Supprimer cette ligne vide</button>
    </form>
@endif
@endsection
