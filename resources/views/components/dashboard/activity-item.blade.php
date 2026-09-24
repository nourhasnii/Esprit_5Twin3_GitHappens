@props(['activity'])
<a href="{{ $activity['route'] }}" class="flex items-center gap-3 border-b border-ink/8 py-3.5 last:border-0 dark:border-white/10">
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest dark:bg-emerald-300/10 dark:text-emerald-300"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg></span>
    <span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold text-ink dark:text-white">{{ $activity['label'] }}</span><span class="block truncate text-xs text-ink/50 dark:text-white/50">{{ $activity['subject'] }}</span></span>
    <time class="shrink-0 text-xs text-ink/40 dark:text-white/40" datetime="{{ $activity['date']?->toIso8601String() }}">{{ $activity['date']?->diffForHumans() }}</time>
</a>