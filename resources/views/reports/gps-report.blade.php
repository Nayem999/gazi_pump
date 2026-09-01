@extends('layouts.admin')

@section('title', 'GPS Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">GPS Report</li>
@endsection

@section('content')
    <x-filter-bar :action="route('reports.gps-report')">
        <div class="col-md-3">
            <label class="form-label">Executive</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach ($executives as $executive)
                    <option value="{{ $executive->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $executive->id)>{{ $executive->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Division</label>
            <select name="division_id" id="filterDivision" class="form-select">
                <option value="">All</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" @selected((string) ($filters['division_id'] ?? '') === (string) $division->id)>{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">District</label>
            <select name="district_id" id="filterDistrict" class="form-select" @disabled(empty($filters['division_id']))>
                <option value="">All</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Thana</label>
            <select name="thana_id" id="filterThana" class="form-select" @disabled(empty($filters['district_id']))>
                <option value="">All</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Territory</label>
            <select name="territory_id" id="filterTerritory" class="form-select" @disabled(empty($filters['thana_id']))>
                <option value="">All</option>
                @foreach ($territories as $territory)
                    <option value="{{ $territory->id }}" @selected((string) ($filters['territory_id'] ?? '') === (string) $territory->id)>{{ $territory->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? now()->startOfMonth()->toDateString() }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? now()->endOfMonth()->toDateString() }}">
        </div>
    </x-filter-bar>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">GPS Report</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('reports.gps-report.export', request()->query()) }}" class="btn btn-outline-secondary"><i class="ti ti-file-spreadsheet me-1"></i>Export</a>
                <a href="{{ route('reports.gps-report.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Executive</th>
                        <th>Territory</th>
                        <th>Ping Count</th>
                        <th>Avg Accuracy</th>
                        <th>Avg Battery</th>
                        <th>Last Seen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row->user?->name }}</td>
                            <td>{{ $row->user?->territory_names ?? '—' }}</td>
                            <td>{{ $row->ping_count }}</td>
                            <td>{{ $row->avg_accuracy !== null ? $row->avg_accuracy.' m' : '—' }}</td>
                            <td>{{ $row->avg_battery_level !== null ? $row->avg_battery_level.'%' : '—' }}</td>
                            <td>{{ $row->last_seen_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No GPS pings recorded for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($rows->hasPages())
            <div class="card-footer">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterDivision = document.getElementById('filterDivision');
            const filterDistrict = document.getElementById('filterDistrict');
            const filterThana = document.getElementById('filterThana');
            const filterTerritory = document.getElementById('filterTerritory');

            initCascadingSelect(filterDivision, filterDistrict, '{{ route('districts.options') }}', 'division_id', {
                initialChildValue: '{{ $filters['district_id'] ?? '' }}',
            });
            initCascadingSelect(filterDistrict, filterThana, '{{ route('thanas.options') }}', 'district_id', {
                initialChildValue: '{{ $filters['thana_id'] ?? '' }}',
            });
        });
    </script>
@endpush
