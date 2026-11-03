@extends('layouts.stock')
@use('App\Support\Fmt')
@use('App\Support\BatchAttributes')
@use('App\Enums\StockMovementType')

@section('eyebrow', __('Inventory workspace'))
@section('title', __('Mouvements de stock'))
@section('description', __('Chaque entrée, sortie, transfert ou ajustement, dans l’ordre où il a eu lieu. Les stocks sont recalculables à partir de cet historique.'))
@section('page-action')
    <a class="btn btn-primary" href="{{ route('stock-movements.create') }}">{{ __('Enregistrer un mouvement') }}</a>
@endsection

@section('content')
<div class="figures">
    @foreach (StockMovementType::cases() as $type)
        @php $t = $totals->get($type->value); @endphp
        <div class="figure">
            <strong>{{ Fmt::q($t->quantity ?? 0) }}</strong>
            <span>{{ __(':labels, :movements mouvement(s)', ['label' => mb_strtolower($type->label()), 'movements' => $t->movements ?? 0]) }}</span>
        </div>
    @endforeach
</div>

<form class="filters" method="GET" action="{{ route('stock-movements.index') }}">
    <div class="field">
        <label for="f-type">{{ __('Type') }}</label>
        <select id="f-type" name="type">
            <option value="">{{ __('Tous') }}</option>
            @foreach (StockMovementType::cases() as $type)
                <option value="{{ $type->value }}" @selected(($filters['type'] ?? null) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-site">{{ __('Site') }}</label>
        <select id="f-site" name="site_id">
            <option value="">{{ __('Tous les sites') }}</option>
            @foreach ($sites as $s)
                <option value="{{ $s->id }}" @selected(($filters['site_id'] ?? null) == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-product">{{ __('Produit') }}</label>
        <select id="f-product" name="product_id">
            <option value="">{{ __('Tous les produits') }}</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}" @selected(($filters['product_id'] ?? null) == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="f-from">{{ __('Du') }}</label>
        <input type="date" id="f-from" name="from" value="{{ $filters['from'] ?? '' }}">
    </div>
    <div class="field">
        <label for="f-to">{{ __('Au') }}</label>
        <input type="date" id="f-to" name="to" value="{{ $filters['to'] ?? '' }}">
    </div>
    <button class="btn btn-ghost" type="submit">{{ __('Filtrer') }}</button>
    @if (array_filter($filters))
        <a class="btn btn-ghost" href="{{ route('stock-movements.index') }}">{{ __('Effacer') }}</a>
    @endif
</form>

<section class="panel">
    @if ($movements->isEmpty())
        <div class="empty">
            <p>{{ __('Aucun mouvement ne correspond à ces critères.') }}</p>
            <a class="btn btn-primary" href="{{ route('stock-movements.create') }}">{{ __('Enregistrer un mouvement') }}</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Référence') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Produit et lot') }}</th>
                    <th>{{ __('Origine') }}</th>
                    <th>{{ __('Destination') }}</th>
                    <th class="num">{{ __('Quantité') }}</th>
                    <th>{{ __('Motif') }}</th>
                    <th>{{ __('Par') }}</th>
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
                            <div class="muted small">{{ $m->batch ? __('Lot ').BatchAttributes::code($m->batch) : __('Sans lot') }}</div>
                        </td>
                        <td>{{ $m->sourceSite?->name ?? '—' }}</td>
                        <td>{{ $m->destinationSite?->name ?? '—' }}</td>
                        <td class="num">{{ Fmt::q($m->quantity) }}</td>
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
