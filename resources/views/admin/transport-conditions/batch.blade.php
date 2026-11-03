<x-dashboard-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-warm">Cold-chain analysis</p>
                <h2 class="mt-1 font-fraunces text-3xl font-bold text-ink dark:text-white">Cold-chain analysis of batch {{ $batch->lot_number }}</h2>
                <p class="mt-2 text-sm text-ink/55 dark:text-white/60">{{ $batch->product?->name ?? 'Product unavailable' }}</p>
            </div>
            <a href="{{ route('admin.transport-conditions.index', ['batch_id' => $batch->id]) }}" class="rounded-xl border border-ink/10 px-4 py-2.5 text-sm font-semibold text-ink/65 dark:border-white/15 dark:text-white/70">Back to conditions</a>
        </div>
    </x-slot>

    @php
        $riskTone = $analysis['risk_level'] === 'high' ? 'rose' : ($analysis['risk_level'] === 'medium' ? 'amber' : 'green');
        $chartWidth = 720;
        $chartHeight = 240;
        $minTemp = -4;
        $maxTemp = 14;
        $points = $analysis['conditions']->values()->map(function ($condition, $index) use ($analysis, $chartWidth, $chartHeight, $minTemp, $maxTemp) {
            $count = max(1, $analysis['conditions']->count() - 1);
            $x = 30 + (($chartWidth - 60) * $index / $count);
            $temperature = (float) $condition->temperature;
            $y = 20 + (($maxTemp - $temperature) / ($maxTemp - $minTemp) * ($chartHeight - 45));
            return ['x' => round($x, 2), 'y' => round(max(20, min($chartHeight - 25, $y)), 2), 'temperature' => $temperature, 'out' => $temperature < 0 || $temperature > 8];
        });
        $polyline = $points->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ');
        $zeroY = 20 + (($maxTemp - 0) / ($maxTemp - $minTemp) * ($chartHeight - 45));
        $eightY = 20 + (($maxTemp - 8) / ($maxTemp - $minTemp) * ($chartHeight - 45));
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="relative"><x-dashboard.kpi-card label="Risk score" :value="number_format($analysis['risk_score'], 2)" detail="Batch score / 100" tone="{{ $riskTone }}" icon="alert" /><span class="absolute right-4 top-4 rounded-full bg-ink/10 px-2 py-1 text-[10px] font-bold uppercase text-ink/60 dark:bg-white/10 dark:text-white/70">{{ $analysis['risk_level'] }}</span></div>
                <x-dashboard.kpi-card label="Average temperature" :value="$analysis['average_temperature'] !== null ? number_format($analysis['average_temperature'], 2) . ' °C' : '—'" detail="All measurements" tone="blue" icon="transit" />
                <x-dashboard.kpi-card label="Max deviation" :value="number_format($analysis['max_deviation'], 2) . ' °C'" detail="Outside 0–8°C" tone="amber" icon="batch" />
                <x-dashboard.kpi-card label="Minutes out of range" :value="$analysis['total_minutes_out_of_range']" detail="Outside 0–8°C" tone="rose" icon="alert" />
                <x-dashboard.kpi-card label="Open alerts" :value="$analysis['alerts']->whereIn('status', ['open', 'acknowledged'])->count()" detail="Active anomaly history" tone="forest" icon="alert" />
            </div>

            <section class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#17241d]">
                <div><h3 class="font-fraunces text-xl font-bold text-ink dark:text-white">Simulator</h3><p class="mt-1 text-sm text-ink/55 dark:text-white/60">Test a temperature excursion without saving a condition or alert.</p></div>
                <form method="POST" action="{{ route('admin.transport-conditions.batch.simulate', $batch) }}" class="mt-5 grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    @csrf
                    <div><label for="simulation_temperature" class="block text-sm font-semibold text-ink dark:text-white">Temperature (°C)</label><input id="simulation_temperature" name="temperature" type="number" step="0.01" min="-50" max="60" required value="{{ old('temperature', $simulationInput['temperature'] ?? '') }}" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm dark:border-white/15 dark:bg-[#17241d] dark:text-white">@error('temperature')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="simulation_duration" class="block text-sm font-semibold text-ink dark:text-white">Duration (minutes)</label><input id="simulation_duration" name="duration_minutes" type="number" min="0" required value="{{ old('duration_minutes', $simulationInput['duration_minutes'] ?? '') }}" class="mt-2 block w-full rounded-xl border-ink/15 bg-white px-4 py-3 text-sm dark:border-white/15 dark:bg-[#17241d] dark:text-white">@error('duration_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <button class="rounded-xl bg-forest px-5 py-3 text-sm font-semibold text-white">Simulate</button>
                </form>
                @if($simulation)
                    <div class="mt-6 rounded-xl border border-ink/8 bg-cream/50 p-5 dark:border-white/10 dark:bg-white/5">
                        <div class="flex flex-wrap items-center justify-between gap-3"><h4 class="font-semibold text-ink dark:text-white">Simulation result</h4><span class="rounded-full px-3 py-1 text-xs font-bold uppercase {{ $simulation['severity'] === 'critical' ? 'bg-red-100 text-red-700' : ($simulation['severity'] === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-700') }}">{{ $simulation['severity'] }}</span></div>
                        <p class="mt-3 text-sm leading-6 text-ink/70 dark:text-white/70">{{ $simulation['explanation'] }}</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3"><div><p class="text-xs uppercase text-ink/45 dark:text-white/45">Risk score</p><p class="mt-1 font-semibold text-ink dark:text-white">{{ number_format($simulation['risk_score'], 2) }} / 100</p></div><div><p class="text-xs uppercase text-ink/45 dark:text-white/45">Temperature points</p><p class="mt-1 font-semibold text-ink dark:text-white">{{ number_format($simulation['temperature_points'], 2) }}</p></div><div><p class="text-xs uppercase text-ink/45 dark:text-white/45">Duration points</p><p class="mt-1 font-semibold text-ink dark:text-white">{{ number_format($simulation['duration_points'], 2) }}</p></div></div>
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#17241d]">
                <div class="flex items-center justify-between gap-4"><div><h3 class="font-fraunces text-xl font-bold text-ink dark:text-white">Temperature timeline</h3><p class="mt-1 text-sm text-ink/55 dark:text-white/60">Red points are outside the analysis range of 0°C to 8°C.</p></div><div class="text-right text-xs text-ink/50 dark:text-white/50"><span class="mr-3">— 0°C</span><span>— 8°C</span></div></div>
                <div class="mt-5 overflow-x-auto">
                    <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="min-w-[640px] w-full" role="img" aria-label="Temperature measurements over time">
                        <line x1="30" y1="{{ $zeroY }}" x2="{{ $chartWidth - 30 }}" y2="{{ $zeroY }}" stroke="#2f855a" stroke-dasharray="5 5" />
                        <line x1="30" y1="{{ $eightY }}" x2="{{ $chartWidth - 30 }}" y2="{{ $eightY }}" stroke="#d97706" stroke-dasharray="5 5" />
                        <line x1="30" y1="20" x2="30" y2="{{ $chartHeight - 25 }}" stroke="currentColor" class="text-ink/20 dark:text-white/20" />
                        <line x1="30" y1="{{ $chartHeight - 25 }}" x2="{{ $chartWidth - 30 }}" y2="{{ $chartHeight - 25 }}" stroke="currentColor" class="text-ink/20 dark:text-white/20" />
                        @if($points->isNotEmpty())<polyline points="{{ $polyline }}" fill="none" stroke="#347a50" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />@endif
                        @foreach($points as $point)<circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" fill="{{ $point['out'] ? '#dc2626' : '#347a50' }}"><title>{{ $point['temperature'] }}°C</title></circle>@endforeach
                    </svg>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-ink/8 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#17241d]">
                    <h3 class="font-fraunces text-xl font-bold text-ink dark:text-white">Why this batch has this score</h3>
                    <ul class="mt-5 space-y-3">@foreach($analysis['reasons'] as $reason)<li class="flex gap-3 text-sm leading-6 text-ink/70 dark:text-white/70"><span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-amber-warm"></span>{{ $reason }}</li>@endforeach</ul>
                </section>
                <section class="rounded-2xl border border-ink/8 bg-white shadow-sm dark:border-white/10 dark:bg-[#17241d]">
                    <div class="border-b border-ink/8 px-6 py-5 dark:border-white/10"><h3 class="font-fraunces text-xl font-bold text-ink dark:text-white">Anomaly history</h3></div>
                    <div class="divide-y divide-ink/8 dark:divide-white/10">@forelse($analysis['alerts'] as $alert)<a href="{{ route('admin.alerts.show', $alert) }}" class="flex items-center justify-between gap-4 px-6 py-4 hover:bg-cream/45 dark:hover:bg-white/5"><div><p class="font-semibold text-ink dark:text-white">{{ $alert->title }}</p><p class="mt-1 text-xs text-ink/55 dark:text-white/60">{{ $alert->created_at?->format('d/m/Y H:i') }} · {{ ucfirst($alert->severity) }} · score {{ $alert->risk_score ?? '—' }}</p></div><x-dashboard.status-badge :status="$alert->status" /></a>@empty<p class="px-6 py-10 text-sm text-ink/55 dark:text-white/60">No alerts recorded for this batch.</p>@endforelse</div>
                </section>
            </div>
        </div>
    </div>
</x-dashboard-layout>
