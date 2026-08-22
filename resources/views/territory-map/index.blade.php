@extends('layouts.admin')

@section('title', 'Territory Map')

@section('breadcrumb')
    <li class="breadcrumb-item active">Territory Map</li>
@endsection

@section('content')
    @php
        $mapData = $territories->map(fn ($territory) => [
            'id' => $territory->id,
            'name' => $territory->name,
            'code' => $territory->code,
            'lat' => $territory->center_lat !== null ? (float) $territory->center_lat : null,
            'lng' => $territory->center_lng !== null ? (float) $territory->center_lng : null,
            'boundary' => $territory->boundary,
            'executiveCount' => $territory->users_count,
        ])->values();
    @endphp

    <x-filter-bar :action="route('territory-map.index')">
        <div class="col-md-2">
            <label class="form-label">Month</label>
            <select name="month" class="form-select">
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" @selected($filters['month'] === $month)>{{ \Illuminate\Support\Carbon::create(2000, $month, 1)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" value="{{ $filters['year'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Division</label>
            <select name="division_id" id="filterDivision" class="form-select">
                <option value="">All Divisions</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" @selected((string) $filters['division_id'] === (string) $division->id)>{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">District</label>
            <select name="district_id" id="filterDistrict" class="form-select" @disabled(empty($filters['division_id']))>
                <option value="">All Districts</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Thana</label>
            <select name="thana_id" id="filterThana" class="form-select" @disabled(empty($filters['district_id']))>
                <option value="">All Thanas</option>
            </select>
        </div>
    </x-filter-bar>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0">Territory Map</h5>
            <div class="d-flex flex-wrap gap-3 small text-muted">
                <span>{{ number_format($mapData->count()) }} territories</span>
                <span>{{ number_format((int) $mapData->sum('executiveCount')) }} executives</span>
                <span>Each territory has its own color. Zoomed-out groups show a count — click a count bubble to zoom in. Zoom in further to see territories filled in with color; click one to see its details.</span>
            </div>
        </div>
        <div class="card-body">
            <div id="territoryMap" style="height:650px;border-radius:.5rem"></div>
        </div>
    </div>

    <x-modal id="territoryDetailModal" title="Territory" size="lg">
        <div id="territoryDetailBody">
            <div class="text-center text-muted py-4">Loading&hellip;</div>
        </div>
    </x-modal>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const territories = @json($mapData);
            const filters = @json($filters);
            const detailUrlBase = '{{ url('/territory-map') }}';

            const filterDivision = document.getElementById('filterDivision');
            const filterDistrict = document.getElementById('filterDistrict');
            const filterThana = document.getElementById('filterThana');

            initCascadingSelect(filterDivision, filterDistrict, '{{ route('districts.options') }}', 'division_id', {
                placeholder: 'All Districts',
                initialChildValue: '{{ $filters['district_id'] }}',
            });
            initCascadingSelect(filterDistrict, filterThana, '{{ route('thanas.options') }}', 'district_id', {
                placeholder: 'All Thanas',
                initialChildValue: '{{ $filters['thana_id'] }}',
            });

            const map = window.L.map('territoryMap').setView([23.8103, 90.4125], 7);

            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            // Deterministic, well-spread hue per territory id (golden-angle
            // rotation) — every territory gets its own distinct color with no
            // lookup table needed, and adjacent ids never land on similar hues.
            function colorForId(id) {
                const hue = (id * 137.508) % 360;
                return `hsl(${hue}, 65%, 45%)`;
            }

            function dotIcon(color, hasExecutives) {
                const size = hasExecutives ? 16 : 11;
                return window.L.divIcon({
                    className: '',
                    html: `<span style="display:block;width:${size}px;height:${size}px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.25)"></span>`,
                    iconSize: [size, size],
                    iconAnchor: [size / 2, size / 2],
                });
            }

            const clusters = window.L.markerClusterGroup({ chunkedLoading: true, maxClusterRadius: 60 });
            const polygons = window.L.featureGroup();

            // Below this zoom, too many territories would be on screen at
            // once for filled shapes to read as anything but a solid blob —
            // dots + cluster counts instead. At/above it, whatever
            // territories are actually in view get their real boundary
            // filled in with color instead of a plain dot.
            const POLYGON_ZOOM_THRESHOLD = 11;
            let boundaryLayer = null;

            function highlightBoundary(t) {
                if (boundaryLayer) {
                    map.removeLayer(boundaryLayer);
                    boundaryLayer = null;
                }
                if (t.boundary) {
                    boundaryLayer = window.L.geoJSON(t.boundary, {
                        style: { color: '#0f172a', weight: 3, fillOpacity: 0, dashArray: '6 4' },
                    }).addTo(map);
                }
            }

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
                        style: { color: '#fff', weight: 1.5, fillColor: color, fillOpacity: 0.65 },
                    });
                    layer.bindTooltip(t.name, { sticky: true });
                    layer.on('click', () => openTerritory(t));
                    layer.on('mouseover', function () { layer.setStyle({ fillOpacity: 0.85, weight: 2.5 }); });
                    layer.on('mouseout', function () { layer.setStyle({ fillOpacity: 0.65, weight: 1.5 }); });
                    polygons.addLayer(layer);
                });
            }

            function syncLayersForZoom() {
                if (map.getZoom() >= POLYGON_ZOOM_THRESHOLD) {
                    if (map.hasLayer(clusters)) {
                        map.removeLayer(clusters);
                    }
                    renderPolygonsInView();
                    if (!map.hasLayer(polygons)) {
                        map.addLayer(polygons);
                    }
                } else {
                    if (map.hasLayer(polygons)) {
                        map.removeLayer(polygons);
                    }
                    if (!map.hasLayer(clusters)) {
                        map.addLayer(clusters);
                    }
                }
            }

            const modalEl = document.getElementById('territoryDetailModal');
            const modalBody = document.getElementById('territoryDetailBody');
            const modalTitle = document.getElementById('territoryDetailModalLabel');
            const modal = new window.bootstrap.Modal(modalEl);

            function money(n) {
                return Number(n || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
            }

            function whatsappNumber(phone) {
                const digits = String(phone || '').replace(/\D/g, '');
                if (!digits) {
                    return null;
                }
                if (digits.startsWith('880')) {
                    return digits;
                }
                if (digits.startsWith('0')) {
                    return '880' + digits.slice(1);
                }
                return digits;
            }

            function phoneActionsHtml(phone) {
                if (!phone) {
                    return '—';
                }
                const wa = whatsappNumber(phone);
                const waLink = wa
                    ? `<a href="https://wa.me/${wa}" target="_blank" rel="noopener" class="btn btn-sm btn-link p-0 text-success" title="Contact on WhatsApp"><i class="ti ti-brand-whatsapp"></i></a>`
                    : '';
                return `<span class="d-inline-flex align-items-center gap-1"><span>${phone}</span><button type="button" class="btn btn-sm btn-link p-0 text-secondary" data-copy="${phone}" title="Copy phone number"><i class="ti ti-copy"></i></button>${waLink}</span>`;
            }

            function renderDetail(payload) {
                const t = payload.territory;
                const c = payload.dealers;

                modalTitle.textContent = `${t.name} (${t.code})`;

                const rows = c.data.map((dealer) => `
                    <tr>
                        <td><a href="${dealer.url}">${dealer.name}</a></td>
                        <td>${dealer.code}</td>
                        <td>${dealer.type}</td>
                        <td>${phoneActionsHtml(dealer.phone)}</td>
                    </tr>
                `).join('');

                modalBody.innerHTML = `
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Division</div>
                            <div class="fw-semibold">${t.divisionName ?? '—'}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">District</div>
                            <div class="fw-semibold">${t.districtName ?? '—'}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Thana</div>
                            <div class="fw-semibold">${t.thanaName ?? '—'}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Manager</div>
                            <div class="fw-semibold">${t.managerName ?? '—'}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Executives</div>
                            <div class="fw-semibold">${t.executiveCount}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Orders (period)</div>
                            <div class="fw-semibold">${money(t.totalOrderValue)}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Collections (period)</div>
                            <div class="fw-semibold">${money(t.totalCollectionAmount)}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Visits (period)</div>
                            <div class="fw-semibold">${t.totalVisits}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">GPS Verified Rate</div>
                            <div class="fw-semibold">${t.gpsVerifiedRate}%</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Achievement Grade</div>
                            <div class="fw-semibold">${t.grade ? `${t.grade} (${t.gradeLabel})` : '—'}</div>
                        </div>
                    </div>
                    <h6 class="mb-2">Dealers ${c.total ? `(${c.total})` : ''}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Name</th><th>Code</th><th>Type</th><th>Phone</th></tr>
                            </thead>
                            <tbody>
                                ${rows || '<tr><td colspan="4" class="text-center text-muted py-3">No dealers in this territory yet.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                    ${c.lastPage > 1 ? `<div class="text-muted small mt-2">Page ${c.currentPage} of ${c.lastPage} — showing first page only.</div>` : ''}
                `;
            }

            function openTerritory(t) {
                highlightBoundary(t);
                modalTitle.textContent = `${t.name} (${t.code})`;
                modalBody.innerHTML = '<div class="text-center text-muted py-4">Loading&hellip;</div>';
                modal.show();

                const url = `${detailUrlBase}/${t.id}?month=${filters.month}&year=${filters.year}`;
                fetch(url, { headers: { Accept: 'application/json' } })
                    .then((r) => r.json())
                    .then(renderDetail)
                    .catch(() => {
                        modalBody.innerHTML = '<div class="text-center text-danger py-4">Failed to load territory details.</div>';
                    });
            }

            territories.forEach(function (t) {
                if (t.lat === null || t.lng === null) {
                    return;
                }

                const marker = window.L.marker([t.lat, t.lng], {
                    icon: dotIcon(colorForId(t.id), t.executiveCount > 0),
                });

                marker.bindTooltip(t.name, { direction: 'top', offset: [0, -6] });
                marker.on('click', () => openTerritory(t));

                clusters.addLayer(marker);
            });

            syncLayersForZoom();
            map.on('zoomend', syncLayersForZoom);
            map.on('moveend', function () {
                if (map.hasLayer(polygons)) {
                    renderPolygonsInView();
                }
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                if (boundaryLayer) {
                    map.removeLayer(boundaryLayer);
                    boundaryLayer = null;
                }
            });
        });
    </script>
@endpush
