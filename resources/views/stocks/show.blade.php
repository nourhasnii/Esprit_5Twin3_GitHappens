@extends('layouts.stock')
@use('App\Support\Fmt')
@use('App\Support\BatchAttributes')

@section('title', __('Historique de stock'))
@section('eyebrow', __('Inventory workspace'))
@section('description', $stock->product?->name.' à '.$stock->site?->name.' · '.($stock->batch ? 'Lot '.BatchAttributes::code($stock->batch) : 'Stock suivi sans lot').(BatchAttributes::expiryDate($stock->batch) ? ' · DLC le '.BatchAttributes::expiryDate($stock->batch)->format('d/m/Y') : ''))
@section('page-action')
    <a class="btn btn-ghost" href="{{ route('stocks.index', ['site_id' => $stock->site_id]) }}">{{ __('Retour aux stocks') }}</a>
    <a class="btn btn-ghost" href="{{ route('stock-movements.create', ['type' => 'adjustment']) }}">{{ __('Ajuster le stock') }}</a>
    @if ($stock->batch && $stock->availableQuantity() > 0)
        <a class="btn btn-primary" href="{{ route('optimization.create', ['batch_id' => $stock->batch_id]) }}">{{ __('Optimiser') }}</a>
    @endif
@endsection

@section('content')
@php $expiry = BatchAttributes::expiryDate($stock->batch); $days = BatchAttributes::daysToExpiry($stock->batch); @endphp

<div class="figures">
    <div class="figure"><strong>{{ Fmt::q($stock->quantity) }}</strong><span>{{ __('en stock sur cette ligne') }}</span></div>
    <div class="figure"><strong>{{ Fmt::q($productAvailable) }}</strong><span>{{ __('disponibles pour ce produit sur le site') }}</span></div>
    <div @class(['figure', 'is-alert' => $stock->min_threshold > 0 && $productAvailable <= $stock->min_threshold])><strong>{{ Fmt::q($stock->min_threshold) }}</strong><span>{{ __('seuil de rupture') }}</span></div>
    <div class="figure"><strong>{{ Fmt::n($avgDaily, 1) }}</strong><span>{{ __('sorties par jour en moyenne sur :window jours', ['window' => $window]) }}</span></div>
</div>

<section class="panel">
    <div class="panel-head"><h2>{{ __('Mouvements de cette ligne') }}</h2></div>
    @if ($movements->isEmpty())
        <p class="empty">{{ __('Aucun mouvement enregistré.') }}</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>{{ __('Date') }}</th><th>{{ __('Référence') }}</th><th>{{ __('Type') }}</th><th>{{ __('Provenance ou destination') }}</th><th class="num">{{ __('Variation') }}</th><th>{{ __('Motif') }}</th><th>{{ __('Par') }}</th></tr>
                </thead>
                <tbody>
                @foreach ($movements as $m)
                    @php $signed = $m->signedQuantityFor($stock->site_id); $other = $signed > 0 ? $m->sourceSite : $m->destinationSite; @endphp
                    <tr>
                        <td class="num" style="text-align:left">{{ $m->moved_at->format('d/m/Y H:i') }}</td>
                        <td class="code">{{ $m->reference }}</td>
                        <td><span class="tag tag-{{ $m->type->value }}">{{ $m->type->label() }}</span></td>
                        <td>{{ $other ? ($signed > 0 ? __('depuis ') : __('vers ')).$other->name : '—' }}</td>
                        <td class="num" style="color: {{ $signed > 0 ? 'var(--pine-deep)' : 'var(--brick)' }}">{{ $signed > 0 ? '+' : '−' }}{{ Fmt::q(abs($signed)) }}</td>
                        <td>{{ $m->reason ?? '—' }}</td>
                        <td class="small">{{ $m->user?->name ?? __('Système') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
<div class="pager">{{ $movements->links('pagination::default') }}</div>
@endsection
