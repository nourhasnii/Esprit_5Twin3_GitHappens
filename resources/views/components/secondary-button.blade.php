<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2.5 bg-white border border-ink/15 rounded-lg font-semibold text-sm text-ink/70 tracking-wide shadow-sm hover:bg-cream/50 hover:text-ink hover:border-ink/30 focus:outline-none focus:ring-2 focus:ring-amber-warm/30 focus:ring-offset-2 disabled:opacity-25 transition-all duration-150']) }}>
    {{ $slot }}
</button>
