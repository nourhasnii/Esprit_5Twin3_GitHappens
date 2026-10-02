@extends('layouts.stock')

@section('title', 'Modifier '.$site->name)

@section('content')
<div class="page-head">
    <div>
        <a class="back" href="{{ route('sites.index') }}">Retour aux sites</a>
        <h1>{{ $site->name }}</h1>
    </div>
</div>

<form method="POST" action="{{ route('sites.update', $site) }}" class="panel">
    @csrf @method('PUT')
    @include('sites._form')
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">Enregistrer les modifications</button>
        <a class="btn btn-ghost" href="{{ route('sites.index') }}">Annuler</a>
    </div>
</form>
@endsection
