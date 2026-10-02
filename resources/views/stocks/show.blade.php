@extends('layouts.stock')
@use('App\Support\Fmt')
@use('App\Support\BatchAttributes')

@section('title', 'Historique de stock')

@section('content')
@php $expiry = BatchAttributes::expiryDate($stock->batch); $days = BatchAttributes::daysToExpiry($stock->batch); @endphp
<div class="page-head">
    <div>
        <a class="back" href="{{ route('stocks.index', ['site_id' => $stock->site_id]) }}">Retour aux stocks du site</a>
        <h1>{{ $stock->product?->name }} à {{ $stock->site?->name }}</h1>
        <p class="lede">
            {{ $stock->batch ? 'Lot '.BatchAttributes::code($stock->batch) : 'Stock suivi sans lot' }}@if ($expiry), DLC le {{ $expiry->format('d/m/Y') }} ({{ $days >= 0 ? 'dans '.$days.' jour(s)' : 'dépassée' }})@endif.
        </p>
    </div>
    <div class="actions">
        <a class="btn btn-ghost" href="{{ route('stock-movements.create', ['type' => 'adjustment']) }}">Ajuster après inventaire</a>
        @if ($stock->batch && $stock->availableQuantity() > 0)
            <a class="btn btn-primary" href="{{ route('optimization.create', ['batch_id' => $stock->batch_id]) }}">Trouver la meilleure destination</a>
        @endif
    </div>
</div>

<div class="figures">
    <div class="figure"><strong>{{ Fmt::q($stock->quantity) }}</strong><span>en stock sur cette ligne</span></div>
    <div class="figure"><strong>{{ Fmt::q($productAvailable) }}</strong><span>disponibles pour ce produit sur le site</span></div>
    <div @class(['figure', 'is-alert' => $stock->min_threshold > 0 && $productAvailable <= $stock->min_threshold])><strong>{{ Fmt::q($stock->min_threshold) }}</strong><span>seuil de rupture</span></div>
    <div class="figure"><strong>{{ Fmt::n($avgDaily, 1) }}</strong><span>sorties par jour en moyenne sur {{ $window }} jours</span></div>
</div>

<section class="panel">
    <div class="panel-head"><h2>Mouvements de cette ligne</h2></div>
    @if ($movements->isEmpty())
        <p class="empty">Aucun mouvement enregistré.</p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Date</th><th>Référence</th><th>Type</th><th>Provenance ou destination</th><th class="num">Variation</th><th>Motif</th><th>Par</th></tr>
                </thead>
                <tbody>
                @foreach ($movements as $m)
                    @php $signed = $m->signedQuantityFor($stock->site_id); $other = $signed > 0 ? $m->sourceSite : $m->destinationSite; @endphp
                    <tr>
                        <td class="num" style="text-align:left">{{ $m->moved_at->format('d/m/Y H:i') }}</td>
                        <td class="code">{{ $m->reference }}</td>
                        <td><span class="tag tag-{{ $m->type->value }}">{{ $m->type->label() }}</span></td>
                        <td>{{ $other ? ($signed > 0 ? 'depuis ' : 'vers ').$other->name : '—' }}</td>
                        <td class="num" style="color: {{ $signed > 0 ? 'var(--pine-deep)' : 'var(--brick)' }}">{{ $signed > 0 ? '+' : '−' }}{{ Fmt::q(abs($signed)) }}</td>
                        <td>{{ $m->reason ?? '—' }}</td>
                        <td class="small">{{ $m->user?->name ?? 'Système' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
<div class="pager">{{ $movements->links('pagination::default') }}</div>
@endsection
