@extends('layouts.stock')

@section('title', __('Nouveau site'))
@section('eyebrow', __('Network workspace'))
@section('description', __('Ajoutez un entrepôt, une plateforme ou un magasin au réseau.'))
@section('page-action')<a class="btn btn-ghost" href="{{ route('sites.index') }}">{{ __('Retour aux sites') }}</a>@endsection

@section('content')
<form method="POST" action="{{ route('sites.store') }}" class="panel">
    @csrf
    @include('sites._form')
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">{{ __('Créer le site') }}</button>
        <a class="btn btn-ghost" href="{{ route('sites.index') }}">{{ __('Annuler') }}</a>
    </div>
</form>
@endsection
