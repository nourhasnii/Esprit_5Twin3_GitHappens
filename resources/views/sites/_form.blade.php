@use('App\Enums\SiteType')
<div class="form-grid">
    <div class="field">
        <label for="code">Code</label>
        <input type="text" id="code" name="code" value="{{ old('code', $site->code) }}" maxlength="30" required placeholder="ENT-SFX">
        @error('code')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="name">Nom</label>
        <input type="text" id="name" name="name" value="{{ old('name', $site->name) }}" required placeholder="Entrepôt frigorifique Sfax">
        @error('name')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="type">Type</label>
        <select id="type" name="type" required>
            @foreach (SiteType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('type', $site->type?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="capacity">Capacité</label>
        <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $site->capacity) }}" min="1" step="any" required>
        <p class="hint">Dans la même unité que les quantités de stock (unités, colis ou kg).</p>
        @error('capacity')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="city">Ville</label>
        <input type="text" id="city" name="city" value="{{ old('city', $site->city) }}">
        @error('city')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="address">Adresse</label>
        <input type="text" id="address" name="address" value="{{ old('address', $site->address) }}">
        @error('address')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="latitude">Latitude</label>
        <input type="number" id="latitude" name="latitude" value="{{ old('latitude', $site->latitude) }}" step="any" min="-90" max="90" placeholder="34.7406">
        @error('latitude')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
        <label for="longitude">Longitude</label>
        <input type="number" id="longitude" name="longitude" value="{{ old('longitude', $site->longitude) }}" step="any" min="-180" max="180" placeholder="10.7603">
        <p class="hint">Sans coordonnées, le site est exclu des recommandations.</p>
        @error('longitude')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field wide">
        <label for="geo-query">Position sur la carte</label>
        <div class="map-tools">
            <input type="search" id="geo-query" placeholder="Rechercher une adresse ou une ville, ex. : Zone industrielle Poudrière, Sfax" autocomplete="off">
            <button type="button" class="btn btn-ghost" id="geo-search">Rechercher</button>
            <button type="button" class="btn btn-ghost" id="geo-locate">Ma position</button>
        </div>
        <div id="site-map" class="map is-small" aria-label="Carte de sélection de la position"></div>
        <p class="map-status" id="geo-status" role="status">Cliquez sur la carte ou déplacez le repère pour renseigner la latitude et la longitude.</p>
    </div>
    <div class="field wide">
        <input type="hidden" name="is_active" value="0">
        <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $site->is_active))> Site actif (reçoit du stock et apparaît dans l’optimisation)</label>
        @error('is_active')<p class="error">{{ $message }}</p>@enderror
    </div>
</div>

@include('partials.map')

@push('scripts')
<script>
    (() => {
        const map = NtMap.create(document.getElementById('site-map'));
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const cityInput = document.getElementById('city');
        const addressInput = document.getElementById('address');
        const query = document.getElementById('geo-query');
        const status = document.getElementById('geo-status');
        const say = (text) => { status.textContent = text; };

        if (!map) {
            document.getElementById('geo-search').disabled = true;
            document.getElementById('geo-locate').disabled = true;
            say('Saisissez la latitude et la longitude à la main.');
            return;
        }

        let marker = null;

        // Place le repère et met à jour les champs (6 décimales ≈ 10 cm)
        const place = (lat, lng, { zoom = null, fill = true } = {}) => {
            if (!marker) {
                marker = L.marker([lat, lng], { draggable: true, icon: NtMap.pinIcon(), autoPan: true }).addTo(map);
                marker.on('dragend', () => {
                    const p = marker.getLatLng();
                    place(p.lat, p.lng);
                    reverse(p.lat, p.lng);
                });
            } else {
                marker.setLatLng([lat, lng]);
            }
            if (fill) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
            }
            if (zoom) map.setView([lat, lng], zoom);
            say(`Position : ${lat.toFixed(5)}, ${lng.toFixed(5)}`);
        };

        // Géocodage OpenStreetMap (Nominatim) : service gratuit, une requête à la fois
        const nominatim = async (path, params) => {
            const url = new URL(`https://nominatim.openstreetmap.org/${path}`);
            Object.entries({ format: 'jsonv2', 'accept-language': 'fr', addressdetails: 1, ...params })
                .forEach(([k, v]) => url.searchParams.set(k, v));
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(response.status);
            return response.json();
        };

        const cityOf = (a = {}) => a.city || a.town || a.village || a.municipality || a.county || a.state || '';

        // Complète la ville et l'adresse si elles sont vides
        const fillPlace = (address, label) => {
            if (!cityInput.value && cityOf(address)) cityInput.value = cityOf(address);
            if (!addressInput.value && label) addressInput.value = label.split(',').slice(0, 3).join(',').trim();
        };

        const reverse = async (lat, lng) => {
            if (cityInput.value && addressInput.value) return;
            try {
                const result = await nominatim('reverse', { lat, lon: lng, zoom: 16 });
                fillPlace(result.address, result.display_name);
            } catch (e) { /* sans importance : les coordonnées sont déjà renseignées */ }
        };

        const search = async () => {
            const q = query.value.trim();
            if (!q) { query.focus(); return; }
            say('Recherche en cours…');
            try {
                const results = await nominatim('search', { q, limit: 1, countrycodes: 'tn' });
                if (!results.length) {
                    say('Aucun lieu trouvé en Tunisie pour « ' + q + ' ». Essayez avec le nom de la ville.');
                    return;
                }
                const r = results[0];
                place(parseFloat(r.lat), parseFloat(r.lon), { zoom: 15 });
                fillPlace(r.address, r.display_name);
                say('Trouvé : ' + r.display_name + '. Ajustez le repère si besoin.');
            } catch (e) {
                say('Recherche impossible pour le moment (connexion ou service indisponible).');
            }
        };

        document.getElementById('geo-search').addEventListener('click', search);
        query.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); search(); }
        });

        document.getElementById('geo-locate').addEventListener('click', () => {
            if (!navigator.geolocation) { say('La géolocalisation n’est pas disponible dans ce navigateur.'); return; }
            say('Localisation en cours…');
            navigator.geolocation.getCurrentPosition(
                (pos) => { place(pos.coords.latitude, pos.coords.longitude, { zoom: 15 }); reverse(pos.coords.latitude, pos.coords.longitude); },
                () => say('Position refusée ou introuvable : autorisez la localisation dans le navigateur.'),
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });

        map.on('click', (e) => { place(e.latlng.lat, e.latlng.lng); reverse(e.latlng.lat, e.latlng.lng); });

        // Saisie manuelle : le repère suit les champs
        const syncFromInputs = () => {
            const lat = parseFloat(latInput.value), lng = parseFloat(lngInput.value);
            if (Number.isFinite(lat) && Number.isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180) {
                place(lat, lng, { zoom: Math.max(map.getZoom(), 12), fill: false });
            }
        };
        latInput.addEventListener('change', syncFromInputs);
        lngInput.addEventListener('change', syncFromInputs);

        syncFromInputs();
    })();
</script>
@endpush
