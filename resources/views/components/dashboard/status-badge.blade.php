@props(['status'])
@php
    $meta = match($status) {
        'verified', 'valid', 'active' => ['label' => strtoupper($status), 'class' => 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300'],
        'pending', 'expiring' => ['label' => $status === 'expiring' ? 'EXPIRING SOON' : 'PENDING', 'class' => 'bg-amber-warm/10 text-amber-warm dark:bg-amber-300/10 dark:text-amber-300'],
        'expired', 'rejected', 'flagged', 'recalled' => ['label' => strtoupper($status), 'class' => 'bg-red-500/10 text-red-700 dark:bg-red-400/10 dark:text-red-300'],
        default => ['label' => strtoupper($status), 'class' => 'bg-ink/8 text-ink/60 dark:bg-white/10 dark:text-white/60'],
    };
@endphp
<span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold tracking-wider {{ $meta['class'] }}">{{ $meta['label'] }}</span>
