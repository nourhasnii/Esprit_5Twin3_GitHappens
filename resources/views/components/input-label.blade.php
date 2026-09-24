@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-ink/80 mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
