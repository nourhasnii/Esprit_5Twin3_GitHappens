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
                    <div class="md:col-span-2">
                        <div class="mx-auto flex min-h-[240px] max-w-2xl items-center justify-center rounded-xl border border-ink/10 bg-cream/40 p-6 dark:border-white/10 dark:bg-white/5">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="max-h-[420px] w-full rounded-lg object-contain">
                            @else
                                <div class="flex flex-col items-center gap-3 text-ink/35 dark:text-white/35">
                                    <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <path d="M21 15L16 10L5 21"/>
                                    </svg>
                                    <p class="text-sm">Aucune image disponible</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-4 text-lg font-semibold">Informations générales</h3>
                        <dl class="space-y-3">
                            <div><dt class="text-sm font-medium text-gray-500">Nom</dt><dd>{{ $product->name }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Description</dt><dd>{{ $product->description ?: '-' }}</dd></div>
                            <div><dt class="text-sm font-medium text-gray-500">Catégorie</dt><dd>{{ $product->categoryModel?->name ?? $product->category }}</dd></div>
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
