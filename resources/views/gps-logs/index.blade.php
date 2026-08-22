@extends('layouts.admin')

@section('title', 'GPS Tracking')

@section('breadcrumb')
    <li class="breadcrumb-item active">GPS Tracking</li>
@endsection

@php
    $points = $logs->map(fn ($log) => [(float) $log->lat, (float) $log->lng])->values();
@endphp

@section('content')
    <x-filter-bar :action="route('gps-logs.index')">
        <div class="col-md-4">
            <label class="form-label">Employee</label>
            <select name="user_id" class="form-select">
                @foreach ($executives as $executive)
                    <option value="{{ $executive->id }}" @selected($selectedUser?->id === $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-map-pin" label="Pings" value="{{ $logs->count() }}" color="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-route" label="Distance Traveled" value="{{ $distanceKm }} km" color="success" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-player-play" label="First Ping" value="{{ $logs->first()?->recorded_at?->format('H:i') ?? '—' }}" color="info" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card icon="ti-player-stop" label="Last Ping" value="{{ $logs->last()?->recorded_at?->format('H:i') ?? '—' }}" color="warning" />
        </div>
    </div>

    <div class="card mb-4 hover-lift">
        <div class="card-header bg-white">
            Travel Route
            @if ($selectedUser)
                &mdash; {{ $selectedUser->name }} on {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('M d, Y') }}
            @endif
        </div>
        <div class="card-body">
            @if ($points->isNotEmpty())
                <div id="gpsRouteMap" style="height:400px;border-radius:.5rem"></div>
            @else
                <p class="text-muted mb-0">No GPS pings recorded for this employee on this date.</p>
            @endif
        </div>
    </div>

    <form id="bulkForm" method="POST" action="{{ route('gps-logs.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected GPS logs?">
        @csrf
        <x-data-table
            title="Ping Log"
            :export-url="auth()->user()->can('export', \App\Models\GpsLog::class) ? route('gps-logs.export', request()->query()) : null"
            :print-url="auth()->user()->can('print', \App\Models\GpsLog::class) ? route('gps-logs.print', request()->query()) : null"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Time</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Accuracy</th>
                    <th>Speed</th>
                    <th>Battery</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($logs as $log)
                <tr>
                    <td>
                        @if (! $log->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $log->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $log->recorded_at->format('H:i:s') }}</td>
                    <td>{{ $log->lat }}</td>
                    <td>{{ $log->lng }}</td>
                    <td>{{ $log->accuracy !== null ? $log->accuracy.' m' : '—' }}</td>
                    <td>{{ $log->speed !== null ? $log->speed.' km/h' : '—' }}</td>
                    <td>{{ $log->battery_level !== null ? $log->battery_level.'%' : '—' }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($log->trashed())
                                @can('restore', $log)
                                    <form method="POST" action="{{ route('gps-logs.restore', $log->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $log)
                                    <form method="POST" action="{{ route('gps-logs.force-destroy', $log->id) }}" data-confirm data-confirm-title="Permanently delete this ping?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('delete', $log)
                                    <form method="POST" action="{{ route('gps-logs.destroy', $log) }}" data-confirm data-confirm-title="Move this ping to trash?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No GPS pings found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($logs as $log)
                    <div class="col">
                        <x-item-card
                            icon="ti-map-pin"
                            icon-color="primary"
                            :title="$log->recorded_at->format('H:i:s')"
                            :subtitle="$log->lat.', '.$log->lng"
                            :status-label="$log->trashed() ? 'Trashed' : null"
                            status-color="danger"
                        >
                            <x-slot:meta>
                                <div>Accuracy: {{ $log->accuracy !== null ? $log->accuracy.' m' : '—' }}</div>
                                <div>Speed: {{ $log->speed !== null ? $log->speed.' km/h' : '—' }}</div>
                                <div>Battery: {{ $log->battery_level !== null ? $log->battery_level.'%' : '—' }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $log->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $log->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($log->trashed())
                                    @can('restore', $log)
                                        <form method="POST" action="{{ route('gps-logs.restore', $log->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $log)
                                        <form method="POST" action="{{ route('gps-logs.force-destroy', $log->id) }}" data-confirm data-confirm-title="Permanently delete this ping?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('delete', $log)
                                        <form method="POST" action="{{ route('gps-logs.destroy', $log) }}" data-confirm data-confirm-title="Move this ping to trash?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="ti ti-trash"></i></button>
                                        </form>
                                    @endcan
                                @endif
                            </x-slot:actions>
                        </x-item-card>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">No GPS pings found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('gps-logs.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>
@endsection

@if ($points->isNotEmpty())
    @push('scripts')
        <script>
            document.getElementById('selectAll')?.addEventListener('change', function () {
                document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
            });

            document.addEventListener('DOMContentLoaded', function () {
                const points = @json($points);
                const map = window.L.map('gpsRouteMap');

                if (points.length > 1) {
                    window.L.polyline(points, { color: '#0d5aa7', weight: 4 }).addTo(map);
                }

                window.L.marker(points[0], { title: 'First ping' }).addTo(map).bindPopup('First ping');
                if (points.length > 1) {
                    window.L.marker(points[points.length - 1], { title: 'Last ping' }).addTo(map).bindPopup('Last ping');
                }

                map.fitBounds(points, { padding: [30, 30] });

                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);
            });
        </script>
    @endpush
@else
    @push('scripts')
        <script>
            document.getElementById('selectAll')?.addEventListener('change', function () {
                document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
            });
        </script>
    @endpush
@endif
