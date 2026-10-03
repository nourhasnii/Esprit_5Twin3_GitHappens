<?php use \App\Support\Fmt; ?>

<?php $__env->startSection('eyebrow', 'Network workspace'); ?>
<?php $__env->startSection('title', 'Sites'); ?>
<?php $__env->startSection('description', 'Gérez les entrepôts, plateformes et magasins du réseau; leurs coordonnées alimentent les calculs de distance.'); ?>
<?php $__env->startSection('page-action'); ?>
    <a class="btn btn-primary" href="<?php echo e(route('sites.create')); ?>">Ajouter un site</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
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
?>

<?php if($sites->isNotEmpty()): ?>
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
            <?php if($missing > 0): ?>
                <span style="color:var(--amber)"><?php echo e($missing); ?> site(s) sans coordonnées GPS ne figurent pas sur la carte.</span>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<section class="panel">
    <?php if($sites->isEmpty()): ?>
        <div class="empty">
            <p>Aucun site pour l’instant. Commencez par déclarer vos entrepôts et magasins.</p>
            <a class="btn btn-primary" href="<?php echo e(route('sites.create')); ?>">Ajouter un site</a>
        </div>
    <?php else: ?>
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
                <?php $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses(['is-off' => ! $site->is_active]); ?>">
                        <td class="code"><?php echo e($site->code); ?></td>
                        <td>
                            <?php echo e($site->name); ?>

                            <?php if($site->city): ?><div class="muted small"><?php echo e($site->city); ?></div><?php endif; ?>
                        </td>
                        <td><?php echo e($site->type->label()); ?></td>
                        <td class="small">
                            <?php if($site->hasCoordinates()): ?>
                                <?php echo e(Fmt::n($site->latitude, 4)); ?>, <?php echo e(Fmt::n($site->longitude, 4)); ?>

                            <?php else: ?>
                                <span class="tag tag-expiring">À renseigner</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $__env->make('stocks._rack', ['used' => $site->usedCapacity(), 'capacity' => $site->capacity], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                        <td><span class="tag <?php echo e($site->is_active ? 'tag-ok' : ''); ?>"><?php echo e($site->is_active ? 'Actif' : 'Inactif'); ?></span></td>
                        <td class="row-actions">
                            <a href="<?php echo e(route('stocks.index', ['site_id' => $site->id])); ?>">Stocks</a>
                            <a href="<?php echo e(route('sites.edit', $site)); ?>">Modifier</a>
                            <?php if($site->stocks_count === 0): ?>
                                <form method="POST" action="<?php echo e(route('sites.destroy', $site)); ?>" onsubmit="return confirm('Supprimer ce site ?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="linklike" type="submit">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('partials.map', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    (() => {
        const sites = <?php echo json_encode($mapSites, 15, 512) ?>;
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views/sites/index.blade.php ENDPATH**/ ?>