@extends('layouts.admin')

@section('title', 'Live GPS Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Live GPS Dashboard</li>
@endsection

@section('content')
    <x-filter-bar :action="route('live-gps.index')">
        <div class="col-md-6">
            <label class="form-label">Territory</label>
            <select name="territory_id" class="form-select">
                <option value="">All</option>
                @foreach ($territories as $territory)
                    <option value="{{ $territory->id }}" @selected((string) ($filters['territory_id'] ?? '') === (string) $territory->id)>{{ $territory->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Executive</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach ($executives as $executive)
                    <option value="{{ $executive->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $executive->id)>{{ $executive->name }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0">Live GPS Dashboard</h5>
            <div class="d-flex flex-wrap align-items-center gap-3 small">
                <span class="d-inline-flex align-items-center gap-1">
                    <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#16a34a"></span>
                    Online (<span id="onlineCount">0</span>)
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#64748b"></span>
                    Last Known (<span id="staleCount">0</span>)
                </span>
                <span class="text-muted">Refreshes every 15s &mdash; positions older than {{ $staleAfterMinutes }} min are shown as last known.</span>
            </div>
        </div>
        <div class="card-body">
            <div id="liveGpsMap" style="height:600px;border-radius:.5rem"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Executives</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Executive</th>
                            <th>Territory</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th>Speed</th>
                            <th>Battery</th>
                        </tr>
                    </thead>
                    <tbody id="liveGpsTableBody">
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = window.L.map('liveGpsMap').setView([23.8103, 90.4125], 8);

            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            const markers = new Map();
            const tableBody = document.getElementById('liveGpsTableBody');
            const positionsUrl = '{{ route('live-gps.positions', request()->query()) }}';

            function markerColor(position) {
                return position.isStale ? '#64748b' : '#16a34a';
            }

            function popupHtml(position) {
                return `
                    <strong>${position.name}</strong> (${position.employeeId})<br>
                    <span class="text-muted">${position.territory ?? ''}</span>
                    <hr class="my-1">
                    ${position.isStale ? 'Last known' : 'Online'} &mdash; ${Math.round(position.secondsAgo / 60)} min ago<br>
                    ${position.speed !== null ? 'Speed: ' + position.speed + ' km/h<br>' : ''}
                    ${position.batteryLevel !== null ? 'Battery: ' + position.batteryLevel + '%' : ''}
                `;
            }

            function renderTable(positions) {
                if (positions.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No executives match the selected filters.</td></tr>';
                    return;
                }

                tableBody.innerHTML = positions.map(function (position) {
                    const minutesAgo = Math.round(position.secondsAgo / 60);
                    const statusBadge = position.isStale
                        ? '<span class="badge text-bg-secondary">Last Known</span>'
                        : '<span class="badge text-bg-success">Online</span>';

                    return `
                        <tr>
                            <td>${position.name}<div class="text-muted small">${position.employeeId}</div></td>
                            <td>${position.territory ?? '—'}</td>
                            <td>${statusBadge}</td>
                            <td>${minutesAgo} min ago</td>
                            <td>${position.speed !== null ? position.speed + ' km/h' : '—'}</td>
                            <td>${position.batteryLevel !== null ? position.batteryLevel + '%' : '—'}</td>
                        </tr>
                    `;
                }).join('');
            }

            function refresh() {
                fetch(positionsUrl, { headers: { Accept: 'application/json' } })
                    .then((response) => response.json())
                    .then((payload) => {
                        const positions = payload.data;
                        const seenUserIds = new Set();
                        let onlineCount = 0;
                        let staleCount = 0;

                        positions.forEach(function (position) {
                            seenUserIds.add(position.userId);
                            position.isStale ? staleCount++ : onlineCount++;

                            const latLng = [position.lat, position.lng];
                            let marker = markers.get(position.userId);

                            if (!marker) {
                                marker = window.L.circleMarker(latLng, {
                                    radius: 10,
                                    color: '#fff',
                                    weight: 2,
                                    fillOpacity: 0.9,
                                }).addTo(map);
                                markers.set(position.userId, marker);
                            } else {
                                marker.setLatLng(latLng);
                            }

                            marker.setStyle({ fillColor: markerColor(position) });
                            marker.bindPopup(popupHtml(position));
                        });

                        // Drop markers for executives no longer matching the filters.
                        markers.forEach(function (marker, userId) {
                            if (!seenUserIds.has(userId)) {
                                map.removeLayer(marker);
                                markers.delete(userId);
                            }
                        });

                        document.getElementById('onlineCount').textContent = onlineCount;
                        document.getElementById('staleCount').textContent = staleCount;
                        renderTable(positions);
                    });
            }

            refresh();
            setInterval(refresh, 15000);
        });
    </script>
@endpush
