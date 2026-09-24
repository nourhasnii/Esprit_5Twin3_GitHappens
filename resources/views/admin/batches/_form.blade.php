@csrf

<div class="space-y-8">
    <section>
        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/></svg>
            </div>
            <div><h3 class="font-fraunces text-xl font-bold text-ink">Product Information</h3><p class="mt-1 text-sm text-ink/55">Associez ce lot à un produit de votre catalogue.</p></div>
        </div>
        <label for="product_id" class="block text-sm font-semibold text-ink">Produit <span class="text-amber-warm">*</span></label>
        <select id="product_id" name="product_id" required class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
            <option value="">Sélectionner un produit</option>
            @foreach($products as $productOption)
                <option value="{{ $productOption->id }}" @selected((string) old('product_id', $batch->product_id ?? '') === (string) $productOption->id)>{{ $productOption->name }}{{ $productOption->category ? ' · ' . $productOption->category : '' }}</option>
            @endforeach
        </select>
        @error('product_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>

    <section class="border-t border-ink/8 pt-8">
        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-warm/10 text-amber-warm">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12M6 21h12M7 3v4l5 5-5 5v4M17 3v4l-5 5 5 5v4"/></svg>
            </div>
            <div><h3 class="font-fraunces text-xl font-bold text-ink">Batch Information</h3><p class="mt-1 text-sm text-ink/55">Définissez l'identité et la période de production du lot.</p></div>
        </div>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="lot_number" class="block text-sm font-semibold text-ink">Numéro de lot <span class="text-amber-warm">*</span></label>
                <input id="lot_number" name="lot_number" type="text" required placeholder="LOT-2026-001" value="{{ old('lot_number', $batch->lot_number ?? '') }}" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                @error('lot_number')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="unit" class="block text-sm font-semibold text-ink">Unité <span class="text-amber-warm">*</span></label>
                <input id="unit" name="unit" type="text" required placeholder="kg, litre, pièce..." value="{{ old('unit', $batch->unit ?? 'kg') }}" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                @error('unit')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="production_date" class="block text-sm font-semibold text-ink">Date de production <span class="text-amber-warm">*</span></label>
                <input id="production_date" name="production_date" type="date" required value="{{ old('production_date', isset($batch) && $batch->production_date ? $batch->production_date->format('Y-m-d') : '') }}" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                @error('production_date')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="expiration_date" class="block text-sm font-semibold text-ink">Date d'expiration <span class="text-amber-warm">*</span></label>
                <input id="expiration_date" name="expiration_date" type="date" required value="{{ old('expiration_date', isset($batch) && $batch->expiration_date ? $batch->expiration_date->format('Y-m-d') : '') }}" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                @error('expiration_date')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="quantity" class="block text-sm font-semibold text-ink">Quantité <span class="text-amber-warm">*</span></label>
                <input id="quantity" name="quantity" type="number" min="0.01" step="0.01" required placeholder="0.00" value="{{ old('quantity', $batch->quantity ?? '') }}" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
                @error('quantity')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="border-t border-ink/8 pt-8">
        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M5 8h10a4 4 0 1 1 0 8H7"/></svg></div>
            <div><h3 class="font-fraunces text-xl font-bold text-ink">Environmental Data</h3><p class="mt-1 text-sm text-ink/55">Ajoutez l'empreinte carbone estimée de ce lot.</p></div>
        </div>
        <label for="carbon_footprint" class="block text-sm font-semibold text-ink">Empreinte carbone <span class="font-normal text-ink/50">(kg CO₂, optionnel)</span></label>
        <input id="carbon_footprint" name="carbon_footprint" type="number" min="0" step="0.01" placeholder="Ex. 12.50" value="{{ old('carbon_footprint', $batch->carbon_footprint ?? '') }}" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm shadow-sm focus:border-amber-warm focus:ring-amber-warm/20">
        @error('carbon_footprint')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>

    <section class="border-t border-ink/8 pt-8">
        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-warm/10 text-amber-warm"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
            <div><h3 class="font-fraunces text-xl font-bold text-ink">Status</h3><p class="mt-1 text-sm text-ink/55">Indiquez l'état actuel de ce lot dans la chaîne.</p></div>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            @foreach(['active' => 'Active', 'expired' => 'Expired', 'recalled' => 'Recalled'] as $statusValue => $statusLabel)
                <label class="cursor-pointer"><input type="radio" name="status" value="{{ $statusValue }}" class="peer sr-only" @checked(old('status', $batch->status ?? 'active') === $statusValue)><span class="flex items-center justify-center rounded-xl border-2 border-ink/10 bg-white px-4 py-3 text-sm font-semibold text-ink/65 transition peer-checked:border-forest peer-checked:bg-forest/5 peer-checked:text-forest hover:border-ink/25">{{ $statusLabel }}</span></label>
            @endforeach
        </div>
        @error('status')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>
</div>

<div class="mt-8 flex flex-col-reverse gap-3 border-t border-ink/8 pt-6 sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ route('admin.batches.index') }}" class="rounded-xl px-5 py-3 text-center text-sm font-semibold text-ink/60 transition hover:bg-ink/5 hover:text-ink">Annuler</a>
    <button type="submit" class="rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-dark">{{ $submitLabel }}</button>
</div>
