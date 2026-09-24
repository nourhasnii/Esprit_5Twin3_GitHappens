@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2.5 border-l-4 border-amber-warm text-start text-base font-medium text-forest bg-amber-warm/5 focus:outline-none focus:text-forest focus:bg-amber-warm/10 focus:border-amber-warm transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2.5 border-l-4 border-transparent text-start text-base font-medium text-ink/70 hover:text-ink hover:bg-cream hover:border-ink/15 focus:outline-none focus:text-ink focus:bg-cream focus:border-ink/15 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
