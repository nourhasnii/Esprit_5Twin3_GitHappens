
<?php if (! $__env->hasRenderedOnce('595b9090-7c6b-466c-ad96-cffcbbcdbf72')): $__env->markAsRenderedOnce('595b9090-7c6b-466c-ad96-cffcbbcdbf72'); ?>
<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
    .map { height: 400px; border-radius: 8px; border: 1px solid var(--rule); background: var(--slot); position: relative; z-index: 0; }
    .map.is-small { height: 320px; }
    .map-offline { display: flex; align-items: center; justify-content: center; padding: 24px; text-align: center; color: var(--ink-soft); font-size: .9rem; }
    .map-legend { display: flex; flex-wrap: wrap; gap: 6px 18px; margin-top: 12px; font-size: .85rem; color: var(--ink-soft); }
    .map-legend i { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 6px; vertical-align: -1px; box-shadow: 0 0 0 2px #fff, 0 0 0 3px rgba(18, 48, 43, .25); }
    .map-legend i.line { width: 22px; height: 0; border-radius: 0; border-top: 3px solid; box-shadow: none; vertical-align: 3px; }
    .map-legend i.line.dashed { border-top-style: dashed; }
    .leaflet-container { font: 400 13px/1.45 var(--font); }
    .leaflet-tile-pane { filter: saturate(.55) brightness(1.03); }
    .leaflet-popup-content { margin: 11px 14px; color: var(--ink); }
    .leaflet-popup-content b { font-weight: 700; }
    .leaflet-popup-content .muted { color: var(--ink-soft); }
    .map-label { background: rgba(255, 255, 255, .92); border: 1px solid var(--rule); border-radius: 4px; box-shadow: none; padding: 1px 6px; font: 700 11px/1.4 var(--font); color: var(--ink); }
    .map-label::before { display: none; }
    .pin-icon span { display: block; box-sizing: border-box; width: 24px; height: 24px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); background: var(--pine); border: 3px solid #fff; box-shadow: 0 2px 6px rgba(0, 0, 0, .35); }
    .map-tools { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 10px; }
    .map-tools input { flex: 1 1 260px; }
    .map-status { font-size: .85rem; color: var(--ink-soft); min-height: 1.3em; margin-top: 8px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPrepend('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    window.NtMap = {
        /** Couleurs alignées sur la palette de l'interface */
        colors: { pine: '#0f6b58', amber: '#c48a1a', brick: '#a3261d', sky: '#245d8a', ink: '#12302b', grey: '#8a9a95', violet: '#6b4f9a' },

        /** Crée une carte centrée sur la Tunisie, ou affiche un message si Leaflet n'a pas pu être chargé. */
        create(el) {
            if (!el) return null;
            if (!window.L) {
                el.classList.add('map-offline');
                el.textContent = 'Carte indisponible : vérifiez la connexion Internet (fond de carte OpenStreetMap).';
                return null;
            }
            const map = L.map(el, { scrollWheelZoom: false }).setView([34.3, 9.8], 6);
            L.tileLayer(<?php echo json_encode(config('stock.map.tiles', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'), 512) ?>, {
                maxZoom: 19,
                subdomains: 'abc',
                attribution: <?php echo json_encode(config('stock.map.attribution', '&copy; OpenStreetMap'), 512) ?>,
            }).addTo(map);
            // La molette ne zoome qu'après un clic sur la carte (évite de bloquer le défilement de la page)
            map.once('click', () => map.scrollWheelZoom.enable());
            return map;
        },

        /**
         * Cadre la carte sur les points. Recommence une fois la page complètement chargée
         * et au premier redimensionnement, pour ne pas garder un zoom calculé trop tôt.
         */
        fit(map, points, options = {}) {
            const opts = { padding: [25, 25], maxZoom: 11, ...options };
            const apply = () => {
                map.invalidateSize();
                if (points.length === 1) {
                    map.setView(points[0], opts.maxZoom);
                } else if (points.length > 1) {
                    map.fitBounds(points, opts);
                }
            };
            apply();
            requestAnimationFrame(apply);
            if (document.readyState !== 'complete') {
                window.addEventListener('load', apply, { once: true });
            }
            if ('ResizeObserver' in window) {
                let initial = true;
                const observer = new ResizeObserver(() => {
                    if (initial) { initial = false; return; }
                    apply();
                    observer.disconnect();
                });
                observer.observe(map.getContainer());
            }
        },

        /** Affiche les étiquettes secondaires seulement à partir d'un certain zoom (évite les chevauchements). */
        labelsFromZoom(map, markers, minZoom) {
            const toggle = () => markers.forEach((m) => (map.getZoom() >= minZoom ? m.openTooltip() : m.closeTooltip()));
            map.on('zoomend', toggle);
            toggle();
        },

        esc(value) {
            return String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        },

        fmt(value, digits = 0) {
            return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: digits }).format(value);
        },

        pinIcon() {
            return L.divIcon({ className: 'pin-icon', html: '<span></span>', iconSize: [24, 24], iconAnchor: [12, 29], popupAnchor: [0, -28] });
        },
    };
</script>
<?php $__env->stopPrepend(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\mdain\nutritrace1\nutritrace\resources\views\partials\map.blade.php ENDPATH**/ ?>