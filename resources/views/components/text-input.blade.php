@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-ink/15 focus:border-amber-warm focus:ring-amber-warm/30 rounded-lg shadow-sm bg-white text-ink placeholder-ink/30 transition-colors duration-150']) }}>
