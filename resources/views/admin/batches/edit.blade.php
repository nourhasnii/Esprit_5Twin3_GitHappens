<x-dashboard-layout>
    <x-slot name="header">
        <div><p class="text-sm font-semibold uppercase tracking-widest text-amber-warm">Traceability workspace</p><h2 class="mt-1 font-fraunces text-3xl font-bold text-ink">Edit Batch</h2></div>
    </x-slot>
    <div class="py-10 sm:py-12"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8"><div class="mb-8"><p class="max-w-2xl text-sm leading-6 text-ink/60">Mettez à jour les informations opérationnelles et environnementales de ce lot.</p></div><div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm sm:p-8"><form action="{{ route('admin.batches.update', $batch) }}" method="POST" novalidate>@method('PUT') @include('admin.batches._form', ['submitLabel' => 'Save changes'])</form></div></div></div>
</x-dashboard-layout>
