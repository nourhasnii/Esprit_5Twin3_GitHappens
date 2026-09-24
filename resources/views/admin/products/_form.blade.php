@csrf

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-white/80">Nom du produit</label>
        <input id="name" type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-[#E3A23C] focus:ring-[#E3A23C]/30 dark:border-white/10 dark:bg-[#1E3527] dark:text-white dark:placeholder:text-white/40">
        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-white/80">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-[#E3A23C] focus:ring-[#E3A23C]/30 dark:border-white/10 dark:bg-[#1E3527] dark:text-white dark:placeholder:text-white/40">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="category" class="block text-sm font-medium text-gray-700">Catégorie</label>
        <input id="category" type="text" name="category" value="{{ old('category', $product->category ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('category')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="unit" class="block text-sm font-medium text-gray-700">Unité</label>
        <input id="unit" type="text" name="unit" value="{{ old('unit', $product->unit ?? 'kg') }}" required placeholder="kg, litre, pièce..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('unit')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="origin_country" class="block text-sm font-medium text-gray-700">Pays d'origine</label>
        <input id="origin_country" type="text" name="origin_country" value="{{ old('origin_country', $product->origin_country ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('origin_country')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="origin_region" class="block text-sm font-medium text-gray-700">Région d'origine</label>
        <input id="origin_region" type="text" name="origin_region" value="{{ old('origin_region', $product->origin_region ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('origin_region')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="producer_id" class="block text-sm font-medium text-gray-700">Producteur</label>
        <select id="producer_id" name="producer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Sélectionner un producteur</option>
            @foreach($producers as $producer)
                <option value="{{ $producer->id }}" @selected((string) old('producer_id', $product->producer_id ?? '') === (string) $producer->id)>{{ $producer->name }}</option>
            @endforeach
        </select>
        @error('producer_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="barcode" class="block text-sm font-medium text-gray-700">Code-barres</label>
        <input id="barcode" type="text" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('barcode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="image" class="block text-sm font-medium text-gray-700">Image (URL)</label>
        <input id="image" type="url" name="image" value="{{ old('image', $product->image ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('image')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="carbon_footprint" class="block text-sm font-medium text-gray-700">Empreinte carbone</label>
        <input id="carbon_footprint" type="number" step="0.01" min="0" name="carbon_footprint" value="{{ old('carbon_footprint', $product->carbon_footprint ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('carbon_footprint')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="verification_status" class="block text-sm font-medium text-gray-700">Statut de vérification</label>
        <select id="verification_status" name="verification_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach(['pending' => 'En attente', 'verified' => 'Vérifié', 'rejected' => 'Rejeté'] as $value => $label)
                <option value="{{ $value }}" @selected(old('verification_status', $product->verification_status ?? 'pending') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('verification_status')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>

    <label class="flex items-center gap-3 md:col-span-2">
        <input type="checkbox" name="is_organic" value="1" @checked(old('is_organic', $product->is_organic ?? false)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <span class="text-sm font-medium text-gray-700">Produit biologique</span>
    </label>
</div>

<div class="mt-8 flex items-center justify-end gap-4">
    <a href="{{ route('admin.products.index') }}" class="text-ink/65 hover:text-forest">Annuler</a>
    <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">{{ $submitLabel }}</button>
</div>
