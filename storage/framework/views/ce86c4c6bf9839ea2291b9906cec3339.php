<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'description', 'href', 'icon' => 'product']));

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

foreach (array_filter((['title', 'description', 'href', 'icon' => 'product']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<a href="<?php echo e($href); ?>" class="group flex min-h-44 flex-col justify-between rounded-2xl border border-ink/8 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-forest/25 hover:shadow-lg dark:border-white/10 dark:bg-[#1b2923] dark:hover:border-emerald-300/30">
    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EEF3EC] text-[#5B8A5F]">
        <?php if($icon === 'certification'): ?><svg class="h-7 w-7" viewBox="0 0 32 32" fill="none"><path d="M16 4 19 7l5-.5.5 5 3 3-3 3-.5 5-5-.5-3 3-3-3-5 .5-.5-5-3-3 3-3 .5-5 5 .5 3-3Z" fill="currentColor"/><path d="m11 16 3 3 7-7" stroke="#EEF3EC" stroke-width="2" stroke-linecap="round"/></svg>
        <?php elseif($icon === 'traceability'): ?><svg class="h-7 w-7" viewBox="0 0 32 32" fill="none"><path d="M4 10h24v14H4zM4 10l4-4h16l4 4M10 15h12M10 19h7" fill="currentColor"/><path d="M4 10h24M16 10v14" stroke="#EEF3EC" stroke-width="1.5"/></svg>
        <?php elseif($icon === 'verification'): ?><svg class="h-7 w-7" viewBox="0 0 32 32" fill="none"><path d="M16 4 27 9v7c0 7-4.5 10.5-11 12-6.5-1.5-11-5-11-12V9l11-5Z" fill="currentColor"/><path d="m10 16 4 4 8-8" stroke="#EEF3EC" stroke-width="2" stroke-linecap="round"/></svg>
        <?php else: ?><svg class="h-7 w-7" viewBox="0 0 32 32" fill="none"><path d="M16 27C7 25 6 17 9 10c3 2 5 4 6 7 1-7 5-11 10-13 1 10-1 19-9 23Z" fill="currentColor"/><path d="M16 27c0-6 1-11 7-17" stroke="#EEF3EC" stroke-width="1.5" stroke-linecap="round"/></svg><?php endif; ?>
    </span>
    <span class="mt-8 flex items-end justify-between gap-4"><span><span class="block font-fraunces text-xl font-semibold text-ink dark:text-white"><?php echo e($title); ?></span><span class="mt-1 block max-w-xs text-sm leading-5 text-ink/55 dark:text-white/55"><?php echo e($description); ?></span></span><span class="text-xl text-ink/30 transition group-hover:translate-x-1 group-hover:text-forest dark:text-white/30 dark:group-hover:text-emerald-300" aria-hidden="true">→</span></span>
</a><?php /**PATH C:\Users\mdain\nutritrace\resources\views\components\dashboard\quick-action.blade.php ENDPATH**/ ?>