@extends('layouts.stock')
@use('App\Support\Fmt')

@section('eyebrow', 'Network workspace')
@section('title', 'Sites')
@section('description', 'Gérez les entrepôts, plateformes et magasins du réseau; leurs coordonnées alimentent les calculs de distance.')
@section('page-action')
    <a class="btn btn-primary" href="{{ route('sites.create') }}">Ajouter un site</a>
@endsection

@section('content')
@php
    $mapSites = $sites->filter->hasCoordinates()->map(fn ($site) => [
        'id' => $site->id,
        'code' => $site->code,
        'name' => $site->name,
        'city' => $site->city,
        'type' => $site->type->value,
        'type_label' => $site->type->label(),
        'lat' => $site->latitude,
        'lng' => $site->longitude,
        'used' => $site->usedCapacity(),
        'capacity' => $site->capacity,
        'active' => $site->is_active,
        'stocks_url' => route('stocks.index', ['site_id' => $site->id]),
        'edit_url' => route('sites.edit', $site),
    ])->values();
    $missing = $sites->reject->hasCoordinates()->count();
@endphp

@if ($sites->isNotEmpty())
    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Carte du réseau</h2>
                <p class="muted small">Position de chaque site. La taille du point indique la capacité, sa couleur le type de site. Cliquez sur un point pour voir son occupation.</p>
            </div>
        </div>
        <div id="sites-map" class="map" aria-label="Carte des sites"></div>
        <div class="map-legend">
            <span><i style="background:#245d8a"></i>Site de production</span>
            <span><i style="background:#0f6b58"></i>Entrepôt</span>
            <span><i style="background:#6b4f9a"></i>Centre de distribution</span>
            <span><i style="background:#c48a1a"></i>Magasin</span>
            <span><i style="background:#a8325e"></i>Association (dons)</span>
            <span><i style="background:#8a9a95"></i>Inactif</span>
            @if ($missing > 0)
                <span style="color:var(--amber)">{{ $missing }} site(s) sans coordonnées GPS ne figurent pas sur la carte.</span>
            @endif
        </div>
    </section>
@endif

<section class="panel">
    @if ($sites->isEmpty())
        <div class="empty">
            <p>Aucun site pour l’instant. Commencez par déclarer vos entrepôts et magasins.</p>
            <a class="btn btn-primary" href="{{ route('sites.create') }}">Ajouter un site</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Site</th>
                    <th>Type</th>
                    <th>Position GPS</th>
                    <th>Occupation</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($sites as $site)
                    <tr @class(['is-off' => ! $site->is_active])>
                        <td class="code">{{ $site->code }}</td>
                        <td>
                            {{ $site->name }}
                            @if ($site->city)<div class="muted small">{{ $site->city }}</div>@endif
                        </td>
                        <td>{{ $site->type->label() }}</td>
                        <td class="small">
                            @if ($site->hasCoordinates())
                                {{ Fmt::n($site->latitude, 4) }}, {{ Fmt::n($site->longitude, 4) }}
                            @else
                                <span class="tag tag-expiring">À renseigner</span>
                            @endif
                        </td>
                        <td>@include('stocks._rack', ['used' => $site->usedCapacity(), 'capacity' => $site->capacity])</td>
                        <td><span class="tag {{ $site->is_active ? 'tag-ok' : '' }}">{{ $site->is_active ? 'Actif' : 'Inactif' }}</span></td>
                        <td class="row-actions">
                            <a href="{{ route('stocks.index', ['site_id' => $site->id]) }}">Stocks</a>
                            <a href="{{ route('sites.edit', $site) }}">Modifier</a>
                            @if ($site->stocks_count === 0)
                                <form method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('Supprimer ce site ?')">
                                    @csrf @method('DELETE')
                                    <button class="linklike" type="submit">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection

@include('partials.map')

@push('scripts')
<script>
    (() => {
        const sites = @json($mapSites);
        const map = NtMap.create(document.getElementById('sites-map'));
        if (!map || sites.length === 0) return;

        const c = NtMap.colors;
        const typeColor = { production: c.sky, warehouse: c.pine, distribution_center: c.violet, store: c.amber, association: '#a8325e' };
        const maxCapacity = Math.max(...sites.map((s) => s.capacity || 0), 1);
        const bounds = [];
        const storeMarkers = [];

        // Les associations sont petites et proches d'autres sites : on les dessine en dernier, par-dessus
        sites.sort((a, b) => (a.type === 'association') - (b.type === 'association'));

        sites.forEach((site) => {
            const rate = site.capacity > 0 ? site.used / site.capacity : 0;
            const color = site.active ? (typeColor[site.type] || c.ink) : c.grey;
            const radius = 7 + 11 * Math.sqrt((site.capacity || 0) / maxCapacity);

            const marker = L.circleMarker([site.lat, site.lng], {
                radius: site.type === 'association' ? 7 : radius,
                color: '#fff', weight: site.type === 'association' ? 3 : 2, fillColor: color, fillOpacity: site.active ? .95 : .5,
            })
                .bindTooltip(NtMap.esc(site.code), { permanent: true, direction: 'right', offset: [radius, 0], className: 'map-label' })
                .bindPopup(
                    `<b>${NtMap.esc(site.name)}</b><br>`
                    + `<span class="muted">${NtMap.esc(site.type_label)}${site.city ? ' · ' + NtMap.esc(site.city) : ''}</span><br>`
                    + (site.type === 'association'
                        ? `Accepte jusqu’à <b>${NtMap.fmt(site.capacity)} u.</b> par don`
                            + `<br><a href="${site.edit_url}">Modifier</a>`
                        : `Occupation : <b>${NtMap.fmt(rate * 100)} %</b> (${NtMap.fmt(site.used)} / ${NtMap.fmt(site.capacity)})`
                            + `<br><a href="${site.stocks_url}">Voir les stocks</a> · <a href="${site.edit_url}">Modifier</a>`)
                    + (site.active ? '' : '<br><span class="muted">Site inactif</span>')
                )
                .addTo(map);

            if (site.type === 'store' || site.type === 'association') storeMarkers.push(marker);
            bounds.push([site.lat, site.lng]);
        });

        NtMap.fit(map, bounds);
        // Magasins et associations sont proches les uns des autres : leur code s'affiche en zoomant
        NtMap.labelsFromZoom(map, storeMarkers, 8);
    })();
</script>
@endpush
