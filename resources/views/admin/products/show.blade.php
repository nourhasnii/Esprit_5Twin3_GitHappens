<x-dashboard-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-warm">Production workspace</p>
                <h2 class="mt-1 font-fraunces text-3xl font-bold text-ink">Détails du produit</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.products.edit', $product) }}" class="rounded bg-indigo-500 px-4 py-2 font-bold text-white hover:bg-indigo-700">Modifier</a>
                <a href="{{ route('admin.products.index') }}" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700">Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="grid grid-cols-1 gap-8 p-6 text-ink dark:text-white md:grid-cols-2">
                    @if($product->image)
                        <div class="md:col-span-2"><img src="{{ $product->image }}" alt="{{ $product->name }}" class="max-h-72 w-full rounded object-contain"></div>
                    @endif
                    <div>
                        <h3 class="mb-4 text-lg font-semibold">Informations générales</h3>
                        <dl class="space-y-3">
                            <div><dt class="text-sm font-medium text-gray-500">Nom</dt><dd>{{ $product->name }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Description</dt><dd>{{ $product->description ?: '-' }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Catégorie</dt><dd>{{ $product->category }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Unité</dt><dd>{{ $product->unit }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Code-barres</dt><dd>{{ $product->barcode ?: '-' }}</dd></div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="mb-4 text-lg font-semibold">Traçabilité</h3>
                        <dl class="space-y-3">
                            <div><dt class="text-sm font-medium text-gray-500">Pays d'origine</dt><dd>{{ $product->origin_country }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Région d'origine</dt><dd>{{ $product->origin_region ?: '-' }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Producteur</dt><dd>{{ $product->producer?->name ?: '-' }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Empreinte carbone</dt><dd>{{ $product->carbon_footprint !== null ? $product->carbon_footprint . ' kg CO₂' : '-' }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Biologique</dt><dd>{{ $product->is_organic ? 'Oui' : 'Non' }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Vérification</dt><dd>{{ ucfirst($product->verification_status) }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
