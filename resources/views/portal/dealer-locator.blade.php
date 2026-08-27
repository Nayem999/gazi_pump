@extends('layouts.portal')

@section('title', 'Dealer Locator')

@section('content')
    @php
        $dealerPoints = $dealers->map(fn ($d) => [
            'name' => $d->name,
            'address' => $d->address,
            'phone' => $d->phone,
            'territory' => $d->territory?->name,
            'lat' => (float) $d->gps_lat,
            'lng' => (float) $d->gps_lng,
        ])->values();

        $serviceCenterPoints = $serviceCenters->map(fn ($s) => [
            'name' => $s->name,
            'address' => $s->address,
            'phone' => $s->phone,
            'lat' => (float) $s->lat,
            'lng' => (float) $s->lng,
        ])->values();

        $territoryPoints = $territories->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'lat' => $t->center_lat !== null ? (float) $t->center_lat : null,
            'lng' => $t->center_lng !== null ? (float) $t->center_lng : null,
            'boundary' => $t->boundary,
        ])->values();
    @endphp

    <div class="container py-5">
        <h1 class="mb-2">Dealer &amp; Service Center Locator</h1>
        <p class="text-muted mb-4">Find an authorized dealer or service center near you. Zoomed-out groups show a count — click a count bubble to zoom in.</p>

        <div class="d-flex gap-3 mb-3 small">
            <span class="d-inline-flex align-items-center gap-1">
                <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#0d5aa7"></span>
                Dealers ({{ $dealers->count() }})
            </span>
            <span class="d-inline-flex align-items-center gap-1">
                <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#16a34a"></span>
                Service Centers ({{ $serviceCenters->count() }})
            </span>
        </div>

        <div id="dealerLocatorMap" style="height:550px;border-radius:.5rem"></div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dealers = @json($dealerPoints);
            const serviceCenters = @json($serviceCenterPoints);
            const territories = @json($territoryPoints);

            const map = window.L.map('dealerLocatorMap').setView([23.8103, 90.4125], 7);
            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            // Same clustering pattern as the admin Territory Map: nearby
            // pins group into a count bubble when zoomed out (so dense
            // areas don't turn into an unreadable pile of overlapping
            // markers), and clicking a bubble zooms in to split it apart.
            const clusters = window.L.markerClusterGroup({ chunkedLoading: true, maxClusterRadius: 50 }).addTo(map);

            // Same golden-angle color trick as the admin Territory Map, so a
            // territory reads the same color on both pages.
            function colorForId(id) {
                const hue = (id * 137.508) % 360;
                return `hsl(${hue}, 65%, 45%)`;
            }

            // Below this zoom the whole country (or a big chunk of it) is on
            // screen — filling every territory in would be a solid smear.
            // Above it, whichever territories are actually in view get their
            // real boundary filled in as a backdrop behind the dealer pins.
            const POLYGON_ZOOM_THRESHOLD = 11;
            const polygons = window.L.featureGroup();
            window.__debugPolygons = polygons;

            function renderPolygonsInView() {
                polygons.clearLayers();
                const bounds = map.getBounds();

                territories.forEach(function (t) {
                    if (t.lat === null || t.lng === null || !t.boundary) {
                        return;
                    }
                    if (!bounds.contains([t.lat, t.lng])) {
                        return;
                    }

                    const color = colorForId(t.id);
                    const layer = window.L.geoJSON(t.boundary, {
                        style: { color: '#fff', weight: 1.5, fillColor: color, fillOpacity: 0.35 },
                    });
                    layer.bindTooltip(t.name, { sticky: true });
                    polygons.addLayer(layer);
                });
            }

            function syncPolygonsForZoom() {
                if (map.getZoom() >= POLYGON_ZOOM_THRESHOLD) {
                    renderPolygonsInView();
                    if (!map.hasLayer(polygons)) {
                        map.addLayer(polygons);
                    }
                } else if (map.hasLayer(polygons)) {
                    map.removeLayer(polygons);
                }
            }

            map.on('zoomend', syncPolygonsForZoom);
            map.on('moveend', function () {
                if (map.hasLayer(polygons)) {
                    renderPolygonsInView();
                }
            });

            function dotIcon(color) {
                return window.L.divIcon({
                    className: '',
                    html: `<span style="display:block;width:14px;height:14px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.25)"></span>`,
                    iconSize: [14, 14],
                    iconAnchor: [7, 7],
                });
            }

            dealers.forEach(function (d) {
                window.L.marker([d.lat, d.lng], { icon: dotIcon('#0d5aa7') })
                    .bindPopup(`<strong>${d.name}</strong><br>${d.territory ?? ''}<br>${d.address ?? ''}<br>${d.phone ?? ''}`)
                    .addTo(clusters);
            });

            serviceCenters.forEach(function (s) {
                window.L.marker([s.lat, s.lng], { icon: dotIcon('#16a34a') })
                    .bindPopup(`<strong>${s.name}</strong><br>${s.address ?? ''}<br>${s.phone ?? ''}`)
                    .addTo(clusters);
            });

            if (clusters.getLayers().length > 0) {
                map.fitBounds(clusters.getBounds(), { padding: [30, 30] });
            }

            syncPolygonsForZoom();
        });
    </script>
@endpush
