@extends('layouts.stock')

@section('title', 'Modifier '.$site->name)
@section('eyebrow', __('Network workspace'))
@section('description', __('Mettez à jour les coordonnées et la capacité de ce site.'))
@section('page-action')<a class="btn btn-ghost" href="{{ route('sites.index') }}">{{ __('Retour aux sites') }}</a>@endsection

@section('content')
<form method="POST" action="{{ route('sites.update', $site) }}" class="panel">
    @csrf @method('PUT')
    @include('sites._form')
    <div class="form-foot">
        <button class="btn btn-primary" type="submit">{{ __('Enregistrer les modifications') }}</button>
        <a class="btn btn-ghost" href="{{ route('sites.index') }}">{{ __('Annuler') }}</a>
    </div>
</form>
@endsection
