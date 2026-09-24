@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-4 py-2 border-b-2 border-amber-warm text-sm font-medium leading-5 text-forest focus:outline-none transition duration-150 ease-in-out bg-amber-warm/5 rounded-t-lg'
            : 'inline-flex items-center px-4 py-2 border-b-2 border-transparent text-sm font-medium leading-5 text-ink/60 hover:text-ink hover:border-ink/20 focus:outline-none focus:text-ink focus:border-ink/20 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
