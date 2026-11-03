@extends('layouts.stock')
@use('App\Support\Fmt')
@use('App\Support\BatchAttributes')

@section('eyebrow', __('Optimization workspace'))
@section('title', __('Recommandations'))
@section('description', __('Classez les destinations selon la demande, la DLC, la distance, la capacité et le CO₂; chaque proposition reste soumise à votre validation.'))
@section('page-action')
    <a class="btn btn-primary" href="{{ route('optimization.create') }}">{{ __('Trouver une destination') }}</a>
@endsection

@section('content')
@include('partials.impact')

<section class="panel">
    @if ($recommendations->isEmpty())
        <div class="empty">
            <p>{{ __('Aucune recommandation pour l’instant. Choisissez un lot en stock pour obtenir sa meilleure destination.') }}</p>
            <a class="btn btn-primary" href="{{ route('optimization.create') }}">{{ __('Trouver une destination pour un lot') }}</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>{{ __('Demandée le') }}</th>
                    <th>{{ __('Lot') }}</th>
                    <th>{{ __('Origine') }}</th>
                    <th>{{ __('Destination recommandée') }}</th>
                    <th class="num">{{ __('Score') }}</th>
                    <th class="num">{{ __('CO₂') }}</th>
                    <th>{{ __('Décision') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($recommendations as $r)
                    <tr>
                        <td style="white-space:nowrap">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="code">{{ BatchAttributes::code($r->batch) }}</span>
                            <div class="muted small">{{ $r->product?->name }}, {{ Fmt::q($r->quantity) }} u.</div>
                        </td>
                        <td>{{ $r->sourceSite?->name ?? '—' }}</td>
                        <td>
                            {{ $r->recommendedSite?->name ?? __('Aucune destination faisable') }}
                            @if ($r->chosen_site_id && $r->chosen_site_id !== $r->recommended_site_id)
                                <div class="muted small">{{ __('Retenu : :name', ['name' => $r->chosenSite?->name]) }}</div>
                            @endif
                        </td>
                        <td class="num"><b>{{ Fmt::n($r->score) }}</b>/100</td>
                        <td class="num">{{ $r->co2_kg !== null ? Fmt::n($r->co2_kg, 1).' kg' : '—' }}</td>
                        <td><span class="tag tag-{{ $r->status->value }}">{{ $r->status->label() }}</span></td>
                        <td class="row-actions"><a href="{{ route('optimization.show', $r) }}">{{ __('Ouvrir') }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
<div class="pager">{{ $recommendations->links('pagination::default') }}</div>
@endsection
