@extends('layouts.stock')
@use('App\Support\Fmt')
@use('App\Support\BatchAttributes')
@use('App\Enums\StockMovementType')

@section('eyebrow', 'Inventory workspace')
@section('title', 'Mouvements de stock')
@section('description', 'Chaque entrée, sortie, transfert ou ajustement, dans l’ordre où il a eu lieu. Les stocks sont recalculables à partir de cet historique.')
@section('page-action')
    <a class="btn btn-primary" href="{{ route('stock-movements.create') }}">Enregistrer un mouvement</a>
@endsection

@section('content')
<div class="figures">
    @foreach (StockMovementType::cases() as $type)
        @php $t = $totals->get($type->value); @endphp
        <div class="figure">
            <strong>{{ Fmt::q($t->quantity ?? 0) }}</strong>
            <span>{{ mb_strtolower($type->label()) }}s, {{ $t->movements ?? 0 }} mouvement(s)</span>
        </div>
    @endforeach
</div>

<form class="filters" method="GET" action="{{ route('stock-movements.index') }}">
    <div class="field">
        <label for="f-type">Type</label>
        <select id="f-type" name="type">
            <option value="">Tous</option>
            @foreach (StockMovementType::cases() as $type)
                <option value="{{ $type->value }}" @selected(($filters['type'] ?? null) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-site">Site</label>
        <select id="f-site" name="site_id">
            <option value="">Tous les sites</option>
            @foreach ($sites as $s)
                <option value="{{ $s->id }}" @selected(($filters['site_id'] ?? null) == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-product">Produit</label>
        <select id="f-product" name="product_id">
            <option value="">Tous les produits</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}" @selected(($filters['product_id'] ?? null) == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-from">Du</label>
        <input type="date" id="f-from" name="from" value="{{ $filters['from'] ?? '' }}">
    </div>
    <div class="field">
        <label for="f-to">Au</label>
        <input type="date" id="f-to" name="to" value="{{ $filters['to'] ?? '' }}">
    </div>
    <button class="btn btn-ghost" type="submit">Filtrer</button>
    @if (array_filter($filters))
        <a class="btn btn-ghost" href="{{ route('stock-movements.index') }}">Effacer</a>
    @endif
</form>

<section class="panel">
    @if ($movements->isEmpty())
        <div class="empty">
            <p>Aucun mouvement ne correspond à ces critères.</p>
            <a class="btn btn-primary" href="{{ route('stock-movements.create') }}">Enregistrer un mouvement</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Référence</th>
                    <th>Type</th>
                    <th>Produit et lot</th>
                    <th>Origine</th>
                    <th>Destination</th>
                    <th class="num">Quantité</th>
                    <th>Motif</th>
                    <th>Par</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($movements as $m)
                    <tr>
                        <td style="white-space:nowrap">{{ $m->moved_at->format('d/m/Y H:i') }}</td>
                        <td class="code">{{ $m->reference }}</td>
                        <td><span class="tag tag-{{ $m->type->value }}">{{ $m->type->label() }}</span></td>
                        <td>
                            {{ $m->product?->name }}
                            <div class="muted small">{{ $m->batch ? 'Lot '.BatchAttributes::code($m->batch) : 'Sans lot' }}</div>
                        </td>
                        <td>{{ $m->sourceSite?->name ?? '—' }}</td>
                        <td>{{ $m->destinationSite?->name ?? '—' }}</td>
                        <td class="num">{{ Fmt::q($m->quantity) }}</td>
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
