@php
    $categoryQuery = app('App\\Models\\Category')->newQuery();
    $uncategorizedProducts = app('App\\Models\\Product')->newQuery();
    $categoryStats = [
        'total' => $categories->total(),
        'active' => (clone $categoryQuery)->where('is_active', true)->count(),
        'uncategorized' => $uncategorizedProducts->whereNull('category_id')->count(),
    ];
@endphp
<x-dashboard-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-warm">Category workspace</p>
                <h1 class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">Catégories</h1>
                <p class="mt-2 text-sm text-ink/55 dark:text-white/55">Organisez les produits du catalogue par famille alimentaire.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-warm px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-light">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nouvelle catégorie
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-300/20 dark:bg-emerald-400/10 dark:text-emerald-300">{{ session('success') }}</div>
        @endif

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3" aria-label="Statistiques catégories">
            @foreach([
                ['label' => 'Total catégories', 'value' => number_format($categoryStats['total'], 0, ',', ' '), 'icon' => '▦', 'tone' => 'bg-forest/10 text-forest'],
                ['label' => 'Catégories actives', 'value' => number_format($categoryStats['active'], 0, ',', ' '), 'icon' => '✓', 'tone' => 'bg-emerald-100 text-emerald-700'],
                ['label' => 'Produits sans catégorie', 'value' => number_format($categoryStats['uncategorized'], 0, ',', ' '), 'icon' => '!', 'tone' => 'bg-amber-100 text-amber-800'],
            ] as $stat)
                <article class="dashboard-stat-card rounded-2xl border p-5 shadow-sm">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold {{ $stat['tone'] }}">{{ $stat['icon'] }}</span>
                    <p class="mt-4 font-fraunces text-3xl font-bold text-ink dark:text-white">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-xs font-medium text-ink/55 dark:text-white/55">{{ $stat['label'] }}</p>
                </article>
            @endforeach
        </section>

        <div class="overflow-hidden rounded-2xl border border-ink/8 bg-white shadow-sm dark:border-white/10 dark:bg-[#1E3527]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink/8 dark:divide-white/10">
                    <thead class="bg-cream/70 dark:bg-[#16281E]"><tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Nom</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Slug</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Produits</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-ink/8 dark:divide-white/10">
                        @forelse($categories as $category)
                            <tr class="transition hover:bg-cream/45 dark:hover:bg-white/5">
                                <td class="px-6 py-4"><p class="font-semibold text-ink dark:text-white">{{ $category->name }}</p>@if($category->description)<p class="mt-1 max-w-xl truncate text-sm text-ink/50 dark:text-white/50">{{ $category->description }}</p>@endif</td>
                                <td class="px-6 py-4 text-sm text-ink/60 dark:text-white/60">{{ $category->slug }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-ink/70 dark:text-white/70">{{ $category->products_count }}</td>
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold {{ $category->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300' : 'bg-ink/10 text-ink/60 dark:bg-white/10 dark:text-white/60' }}"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td class="whitespace-nowrap px-6 py-4 text-right"><div class="flex items-center justify-end gap-1"><a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-ink/50 transition hover:bg-forest/10 hover:text-forest dark:text-white/55" title="Modifier" aria-label="Modifier la catégorie"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg></a><form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ? Les produits associés garderont leur catégorie historique.')">@csrf @method('DELETE')<button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-rose-700 transition hover:bg-rose-50 hover:text-rose-600 dark:text-rose-300 dark:hover:bg-rose-400/10" title="Supprimer" aria-label="Supprimer la catégorie"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4h8v2m2 0-1 14H7L6 6m4 4v6m4-6v6"/></svg></button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-16 text-center"><h2 class="font-fraunces text-xl font-bold text-ink dark:text-white">Aucune catégorie pour le moment</h2><p class="mt-2 text-sm text-ink/55 dark:text-white/55">Créez une catégorie pour organiser les produits.</p><a href="{{ route('admin.categories.create') }}" class="mt-5 inline-flex rounded-xl bg-amber-warm px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest">Créer une catégorie</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())<div class="border-t border-ink/8 p-5 dark:border-white/10">{{ $categories->links() }}</div>@endif
        </div>
    </div>
</x-dashboard-layout>