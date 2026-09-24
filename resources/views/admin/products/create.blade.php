<x-dashboard-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-warm">Production workspace</p>
            <h2 class="mt-1 font-fraunces text-3xl font-bold text-ink">Ajouter un produit</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-ink dark:text-white">
                    <form action="{{ route('admin.products.store') }}" method="POST">
                        @include('admin.products._form', ['submitLabel' => 'Enregistrer'])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
