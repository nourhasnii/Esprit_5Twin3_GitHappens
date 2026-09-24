@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm px-4 py-3 rounded-xl bg-forest/5 border border-forest/10 text-forest']) }}>
        {{ $status }}
    </div>
@endif
