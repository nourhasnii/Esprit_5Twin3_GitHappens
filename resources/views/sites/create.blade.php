@extends('layouts.stock')

@section('title', 'Nouveau site')
@section('eyebrow', 'Network workspace')
@section('description', 'Ajoutez un entrepôt, une plateforme ou un magasin au réseau.')
@section('page-action')<a class="btn btn-ghost" href="{{ route('sites.index') }}">Retour aux sites</a>@endsection

@section('content')
<form method="POST" action="{{ route('sites.store') }}" class="panel">
    @csrf
    @include('sites._form')
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">Créer le site</button>
        <a class="btn btn-ghost" href="{{ route('sites.index') }}">Annuler</a>
    </div>
</form>
@endsection
