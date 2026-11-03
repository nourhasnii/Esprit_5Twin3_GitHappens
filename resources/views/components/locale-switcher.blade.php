{{-- Sélecteur de langue (Français / English / العربية). Variante "dark" pour les fonds foncés. --}}
@props(['variant' => 'light'])
@php
    $locales = config('app.available_locales', []);
    $current = app()->getLocale();
    $short = ['fr' => 'FR', 'en' => 'EN', 'ar' => 'ع'];
@endphp
<div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative" data-locale-switcher>
    <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="true"
            aria-label="{{ __('Changer de langue') }}" title="{{ __('Changer de langue') }}"
            @class([
                'inline-flex h-8 items-center gap-1.5 rounded-full px-2.5 text-xs font-bold transition',
                'text-ink/70 ring-1 ring-ink/10 hover:bg-ink/5 dark:text-white/70 dark:ring-white/10 dark:hover:bg-white/5' => $variant === 'light',
                'text-white/80 ring-1 ring-white/20 hover:bg-white/10' => $variant === 'dark',
            ])>
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3Z"/></svg>
        <span>{{ $short[$current] ?? strtoupper($current) }}</span>
    </button>
    <div x-cloak x-show="open" x-transition.opacity
         class="absolute end-0 z-50 mt-2 w-40 overflow-hidden rounded-xl border border-ink/10 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-[#17221d]">
        @foreach ($locales as $code => $name)
            <a href="{{ route('locale.switch', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}"
               @class([
                   'flex items-center justify-between px-3 py-2 text-sm transition hover:bg-ink/5 dark:hover:bg-white/5',
                   'font-semibold text-forest dark:text-white' => $code === $current,
                   'text-ink/70 dark:text-white/70' => $code !== $current,
               ])
               @if ($code === $current) aria-current="true" @endif>
                <span>{{ $name }}</span>
                @if ($code === $current)<span aria-hidden="true">✓</span>@endif
            </a>
        @endforeach
    </div>
</div>
