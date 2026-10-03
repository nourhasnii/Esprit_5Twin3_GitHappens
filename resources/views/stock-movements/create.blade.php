@extends('layouts.stock')
@use('App\Support\BatchAttributes')
@use('App\Enums\StockMovementType')

@section('title', 'Enregistrer un mouvement')
@section('eyebrow', 'Inventory workspace')
@section('description', 'Chaque mouvement met à jour immédiatement le stock du ou des sites concernés.')
@section('page-action')<a class="btn btn-ghost" href="{{ route('stock-movements.index') }}">Retour aux mouvements</a>@endsection

@section('content')
<form method="POST" action="{{ route('stock-movements.store') }}" class="panel" id="movement-form">
    @csrf
    <fieldset class="choices">
        <legend>Type de mouvement</legend>
        @foreach (StockMovementType::cases() as $type)
            <label class="choice">
                <input type="radio" name="type" value="{{ $type->value }}" @checked(old('type', $defaultType->value) === $type->value)>
                <b>{{ $type->label() }}</b>
                <small>{{ $type->description() }}</small>
            </label>
        @endforeach
    </fieldset>
    @error('type')<p class="error">{{ $message }}</p>@enderror

    <div class="form-grid">
        <div class="field">
            <label for="batch_id">Lot</label>
            <select id="batch_id" name="batch_id">
                <option value="">Sans lot</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}" data-product="{{ $batch->product_id }}" @selected(old('batch_id') == $batch->id)>
                        {{ BatchAttributes::code($batch) }}, {{ $batch->product?->name }}
                        @if ($d = BatchAttributes::expiryDate($batch)) (DLC {{ $d->format('d/m/Y') }}) @endif
                    </option>
                @endforeach
            </select>
            @error('batch_id')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="product_id">Produit</label>
            <select id="product_id" name="product_id">
                <option value="">Choisir un produit</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
            <p class="hint">Rempli automatiquement quand un lot est choisi.</p>
            @error('product_id')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="field" data-show="out transfer">
            <label for="source_site_id">Site d’origine</label>
            <select id="source_site_id" name="source_site_id">
                <option value="">Choisir un site</option>
                @foreach ($sites as $site)
                    <option value="{{ $site->id }}" @selected(old('source_site_id') == $site->id)>{{ $site->name }}</option>
                @endforeach
            </select>
            @error('source_site_id')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field" data-show="in transfer">
            <label for="destination_site_id">Site de destination</label>
            <select id="destination_site_id" name="destination_site_id">
                <option value="">Choisir un site</option>
                @foreach ($sites as $site)
                    <option value="{{ $site->id }}" @selected(old('destination_site_id') == $site->id)>{{ $site->name }}</option>
                @endforeach
            </select>
            @error('destination_site_id')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field" data-show="adjustment">
            <label for="site_id">Site inventorié</label>
            <select id="site_id" name="site_id">
                <option value="">Choisir un site</option>
                @foreach ($sites as $site)
                    <option value="{{ $site->id }}" @selected(old('site_id') == $site->id)>{{ $site->name }}</option>
                @endforeach
            </select>
            @error('site_id')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="field" data-show="in out transfer">
            <label for="quantity">Quantité</label>
            <input type="number" id="quantity" name="quantity" value="{{ old('quantity') }}" min="0.01" step="any">
            @error('quantity')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field" data-show="adjustment">
            <label for="counted_quantity">Quantité comptée</label>
            <input type="number" id="counted_quantity" name="counted_quantity" value="{{ old('counted_quantity') }}" min="0" step="any">
            <p class="hint">Saisissez ce que vous avez réellement compté ; l’écart avec le stock enregistré est calculé automatiquement.</p>
            @error('counted_quantity')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="moved_at">Date du mouvement</label>
            <input type="datetime-local" id="moved_at" name="moved_at" value="{{ old('moved_at', now()->format('Y-m-d\TH:i')) }}" max="{{ now()->format('Y-m-d\TH:i') }}">
            @error('moved_at')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="reason">Motif</label>
            <input type="text" id="reason" name="reason" value="{{ old('reason') }}" maxlength="255" placeholder="Ventes du jour, casse, réassort…">
            @error('reason')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field wide">
            <label for="notes">Commentaire</label>
            <textarea id="notes" name="notes" maxlength="2000">{{ old('notes') }}</textarea>
            @error('notes')<p class="error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="form-foot">
        <button class="btn btn-primary" type="submit">Enregistrer le mouvement</button>
        <a class="btn btn-ghost" href="{{ route('stock-movements.index') }}">Annuler</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('movement-form');
        const sync = () => {
            const type = form.querySelector('input[name="type"]:checked')?.value;
            form.querySelectorAll('[data-show]').forEach((field) => {
                const visible = field.dataset.show.split(' ').includes(type);
                field.hidden = !visible;
                field.querySelectorAll('input, select, textarea').forEach((el) => { el.disabled = !visible; });
            });
        };
        form.querySelectorAll('input[name="type"]').forEach((radio) => radio.addEventListener('change', sync));
        sync();

        const batch = document.getElementById('batch_id');
        const product = document.getElementById('product_id');
        batch.addEventListener('change', () => {
            const id = batch.selectedOptions[0]?.dataset.product;
            if (id) product.value = id;
        });
    })();
</script>
@endpush
