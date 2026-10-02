@extends('layouts.stock')

@section('title', 'Nouveau site')

@section('content')
<div class="page-head">
    <div>
        <a class="back" href="{{ route('sites.index') }}">Retour aux sites</a>
        <h1>Nouveau site</h1>
    </div>
</div>

<form method="POST" action="{{ route('sites.store') }}" class="panel">
    @csrf
    @include('sites._form')
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">Créer le site</button>
        <a class="btn btn-ghost" href="{{ route('sites.index') }}">Annuler</a>
    </div>
</form>
@endsection
