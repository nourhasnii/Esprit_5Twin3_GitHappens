<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('front.products.index') }}" class="text-sm font-semibold text-forest hover:text-amber-warm">{{ __('Products') }}</a>
            <span class="text-ink/30">/</span>
            <h1 class="truncate font-fraunces text-2xl font-semibold text-ink">{{ $product->name }}</h1>
        </div>
    </x-slot>

    <div class="bg-cream py-10 sm:py-14">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-ink/8 bg-white shadow-sm">
                <div class="grid lg:grid-cols-[0.9fr_1.1fr]">
                    <div class="flex min-h-72 items-center justify-center bg-forest/5 lg:min-h-full">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full max-h-[30rem] w-full object-cover">
                        @else
                            <span class="font-fraunces text-8xl font-semibold text-forest/25">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="p-6 sm:p-10">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-forest/10 px-3 py-1 text-xs font-semibold text-forest">{{ $product->category }}</span>
                            @if($product->verification_status === 'verified')
                                <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700">{{ __('Verified product') }}</span>
                            @endif
                        </div>
                        <h2 class="mt-5 font-fraunces text-4xl font-semibold text-ink">{{ $product->name }}</h2>
                        <p class="mt-4 leading-7 text-ink/65">{{ $product->description ?: __('No description has been provided for this product yet.') }}</p>

                        <dl class="mt-8 grid gap-5 border-t border-ink/8 pt-6 sm:grid-cols-2">
                            <div><dt class="text-xs uppercase tracking-wider text-ink/45">{{ __('Origin') }}</dt><dd class="mt-1 font-semibold text-ink">{{ collect([$product->origin_region, $product->origin_country])->filter()->join(', ') ?: __('Not specified') }}</dd></div>
                            <div><dt class="text-xs uppercase tracking-wider text-ink/45">{{ __('Producer') }}</dt><dd class="mt-1 font-semibold text-ink">{{ $product->producer?->name ?: __('Not specified') }}</dd></div>
                            <div><dt class="text-xs uppercase tracking-wider text-ink/45">{{ __('Unit') }}</dt><dd class="mt-1 font-semibold text-ink">{{ $product->unit ?: __('Not specified') }}</dd></div>
                            <div><dt class="text-xs uppercase tracking-wider text-ink/45">{{ __('Lots tracked') }}</dt><dd class="mt-1 font-semibold text-ink">{{ $product->batches->count() }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-ink/8 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-warm">{{ __('Traceability') }}</p><h2 class="mt-1 font-fraunces text-2xl font-semibold text-ink">{{ __('Production lots') }}</h2></div><span class="text-sm text-ink/50">{{ __(':batches total', ['batches' => $product->batches->count()]) }}</span></div>
                <div class="mt-5 divide-y divide-ink/8">
                    @forelse($product->batches as $batch)
                        <div class="flex flex-col justify-between gap-2 py-4 sm:flex-row sm:items-center"><div><p class="font-semibold text-ink">{{ $batch->lot_number }}</p><p class="text-sm text-ink/55">{{ __('Produced :y', ['y' => $batch->production_date?->format('d M Y') ?: '—']) }}</p></div><span class="rounded-full bg-forest/10 px-3 py-1 text-xs font-semibold text-forest">{{ ucfirst($batch->status) }}</span></div>
                    @empty
                        <p class="py-8 text-center text-sm text-ink/50">{{ __('No production lots have been recorded yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>