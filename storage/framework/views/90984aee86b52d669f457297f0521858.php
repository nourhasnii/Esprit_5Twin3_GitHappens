<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['activity']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['activity']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<a href="<?php echo e($activity['route']); ?>" class="flex items-center gap-3 border-b border-ink/8 py-3.5 last:border-0 dark:border-white/10">
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-forest/10 text-forest dark:bg-emerald-300/10 dark:text-emerald-300"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg></span>
    <span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold text-ink dark:text-white"><?php echo e($activity['label']); ?></span><span class="block truncate text-xs text-ink/50 dark:text-white/50"><?php echo e($activity['subject']); ?></span></span>
    <time class="shrink-0 text-xs text-ink/40 dark:text-white/40" datetime="<?php echo e($activity['date']?->toIso8601String()); ?>"><?php echo e($activity['date']?->diffForHumans()); ?></time>
</a><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\components\dashboard\activity-item.blade.php ENDPATH**/ ?>