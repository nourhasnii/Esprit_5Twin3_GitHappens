@php
    $statusClasses = [
        'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300',
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-400/15 dark:text-amber-300',
        'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-400/15 dark:text-rose-300',
    ];
    $decisionClasses = [
        'sell' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-400/15 dark:text-emerald-300',
        'donate' => 'bg-sky-100 text-sky-800 dark:bg-sky-400/15 dark:text-sky-300',
        'withdraw' => 'bg-rose-100 text-rose-800 dark:bg-rose-400/15 dark:text-rose-300',
        'inspect' => 'bg-amber-100 text-amber-800 dark:bg-amber-400/15 dark:text-amber-300',
    ];
    $decisionLabels = ['sell' => 'Vente', 'donate' => 'Don', 'withdraw' => 'Retrait', 'inspect' => 'Contrôle'];
    $collection = $checks->getCollection();
@endphp
<x-dashboard-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-warm">Quality workspace</p>
                <h1 class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">Analyses Qualité</h1>
                <p class="mt-2 text-sm text-ink/55 dark:text-white/55">Évaluez la fraîcheur et la qualité des produits avec l'analyse visuelle.</p>
            </div>
            <a href="{{ route('admin.quality-checks.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-forest px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-forest-dark">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nouvelle analyse
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6">
        @if(session('success'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-300/20 dark:bg-emerald-400/10 dark:text-emerald-300">{{ session('success') }}</div>
        @endif
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['label' => 'Total analyses', 'value' => $checks->total(), 'tone' => 'forest'],
                ['label' => 'Completed', 'value' => $collection->where('status', 'completed')->count(), 'tone' => 'emerald'],
                ['label' => 'Pending', 'value' => $collection->where('status', 'pending')->count(), 'tone' => 'amber'],
                ['label' => 'Failed', 'value' => $collection->where('status', 'failed')->count(), 'tone' => 'rose'],
            ] as $summary)
                <div class="rounded-2xl border border-ink/8 bg-white p-5 shadow-[0_14px_35px_-28px_rgba(31,61,46,0.7)] dark:border-white/10 dark:bg-[#1E3527]">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">{{ $summary['label'] }}</p>
                    <p class="mt-2 font-fraunces text-3xl font-bold {{ $summary['tone'] === 'forest' ? 'text-ink dark:text-white' : 'text-' . $summary['tone'] . '-700 dark:text-' . $summary['tone'] . '-300' }}">{{ $summary['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-ink/8 bg-white shadow-sm dark:border-white/10 dark:bg-[#1E3527]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink/8 dark:divide-white/10">
                    <thead class="bg-cream/70 dark:bg-[#16281E]"><tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Produit</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Score</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Décision</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Confiance</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-ink/45 dark:text-white/45">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-ink/8 dark:divide-white/10">
                        @forelse($checks as $check)
                            <tr class="transition hover:bg-cream/45 dark:hover:bg-white/5">
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-cream ring-1 ring-ink/8 dark:bg-[#16281E] dark:ring-white/10">@if($check->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists(ltrim($check->image_path, '/')))<img src="{{ asset('storage/quality-checks-public/' . basename(ltrim($check->image_path, '/'))) }}" alt="Image analysée" class="h-full w-full object-cover">@else<span class="text-lg text-forest dark:text-emerald-300">◎</span>@endif</div>@if($check->product)<div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-cream ring-1 ring-ink/8 dark:bg-[#16281E] dark:ring-white/10">@if(!empty($check->product->image_url))<img src="{{ $check->product->image_url }}" alt="{{ $check->product->name }}" class="h-10 w-10 rounded-lg object-cover">@else<span class="font-fraunces text-lg font-semibold text-forest/35 dark:text-emerald-300/35">{{ strtoupper(substr($check->product->name, 0, 1)) }}</span>@endif</div>@endif<div class="min-w-0"><p class="truncate font-semibold text-ink dark:text-white">{{ $check->product?->name ?? 'Produit supprimé' }}</p><p class="text-xs text-ink/45 dark:text-white/45">Analyse #{{ $check->id }}</p></div></div></td>
                                <td class="px-6 py-4"><div class="w-28"><div class="flex items-center justify-between gap-2"><span class="font-fraunces text-lg font-bold text-ink dark:text-white">{{ $check->quality_score !== null ? number_format((float) $check->quality_score, 1) : '—' }}</span><span class="text-[10px] text-ink/45 dark:text-white/45">/100</span></div><div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-ink/10 dark:bg-white/10"><div class="h-full rounded-full bg-forest" style="width: {{ min(max((float) ($check->quality_score ?? 0), 0), 100) }}%"></div></div></div></td>
                                <td class="px-6 py-4"><span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $decisionClasses[$check->decision] ?? 'bg-ink/10 text-ink/60 dark:bg-white/10 dark:text-white/60' }}">{{ $decisionLabels[$check->decision] ?? 'En attente' }}</span></td>
                                <td class="px-6 py-4 text-sm font-medium text-ink/65 dark:text-white/65">{{ $check->confidence !== null ? number_format((float) $check->confidence, 1) . '%' : '—' }}</td>
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClasses[$check->status] ?? 'bg-ink/10 text-ink/60 dark:bg-white/10 dark:text-white/60' }}"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ ucfirst($check->status) }}</span></td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-ink/55 dark:text-white/55">{{ $check->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right"><div class="flex items-center justify-end gap-3"><a href="{{ route('admin.quality-checks.show', $check) }}" class="rounded-lg px-2.5 py-1.5 font-semibold text-forest transition hover:bg-forest/10 hover:text-amber-warm dark:text-emerald-300 dark:hover:bg-emerald-300/10">Voir</a><form action="{{ route('admin.quality-checks.destroy', $check) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette analyse ?')">@csrf @method('DELETE')<button class="rounded-lg px-2.5 py-1.5 font-semibold text-rose-700 transition hover:bg-rose-50 hover:text-rose-500 dark:text-rose-300 dark:hover:bg-rose-400/10">Supprimer</button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-16 text-center"><div class="mx-auto max-w-md"><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-forest/10 text-forest dark:bg-emerald-400/10 dark:text-emerald-300">✦</div><h3 class="mt-4 font-fraunces text-xl font-bold text-ink dark:text-white">Aucune analyse pour le moment</h3><p class="mt-2 text-sm text-ink/55 dark:text-white/55">Lancez une première analyse visuelle pour obtenir un score qualité.</p><a href="{{ route('admin.quality-checks.create') }}" class="mt-5 inline-flex rounded-xl bg-amber-warm px-4 py-2.5 text-sm font-semibold text-white hover:bg-forest">Nouvelle analyse</a></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($checks->hasPages())<div class="border-t border-ink/8 p-5 dark:border-white/10">{{ $checks->links() }}</div>@endif
        </div>
    </div>
</x-dashboard-layout>
