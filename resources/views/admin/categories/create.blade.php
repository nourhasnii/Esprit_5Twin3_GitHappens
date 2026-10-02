<x-dashboard-layout>
    <x-slot name="header">
        <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-warm">Product workspace</p><h1 class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">Créer une catégorie</h1></div>
    </x-slot>
    <div class="mx-auto max-w-3xl">
        @if($errors->any())<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-300/20 dark:bg-rose-400/10 dark:text-rose-300"><p class="font-semibold">Vérifiez les informations saisies.</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form action="{{ route('admin.categories.store') }}" method="POST" class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm sm:p-8 dark:border-white/10 dark:bg-[#1E3527]">
            @include('admin.categories._form', ['submitLabel' => 'Créer la catégorie'])
        </form>
    </div>
</x-dashboard-layout>