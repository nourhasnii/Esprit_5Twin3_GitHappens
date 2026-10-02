{{-- Jauge d'occupation d'un site : variables $used et $capacity --}}
@php
    $rate = $capacity > 0 ? $used / $capacity : 0;
    $level = $rate >= 0.9 ? 'is-high' : ($rate >= 0.7 ? 'is-mid' : '');
@endphp
<div class="capacity">
    <div class="rack {{ $level }}" role="img" aria-label="Occupation {{ \App\Support\Fmt::n($rate * 100) }} %">
        <span style="width: {{ min(100, round($rate * 100, 1)) }}%"></span>
    </div>
    <div class="capacity-legend">
        <span><b>{{ \App\Support\Fmt::n($rate * 100) }} %</b> occupé</span>
        <span><b>{{ \App\Support\Fmt::q($used) }}</b> sur {{ \App\Support\Fmt::q($capacity) }}</span>
    </div>
</div>
