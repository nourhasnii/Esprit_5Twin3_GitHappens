<x-dashboard-layout>
    <x-slot name="header"><div><p class="text-sm font-semibold uppercase tracking-widest text-amber-warm">Verification workspace</p><h2 class="mt-1 font-fraunces text-3xl font-bold text-ink">Add Certification</h2><p class="mt-2 text-sm text-ink/55">Register a certification and associate it with a product.</p></div></x-slot>
    <div class="py-10 sm:py-12"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8"><div class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm sm:p-8"><form action="{{ route('admin.certifications.store') }}" method="POST" enctype="multipart/form-data">@include('admin.certifications._form', ['submitLabel' => 'Save Certification'])</form></div></div></div>
</x-dashboard-layout>
