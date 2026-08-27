@extends('layouts.admin')

@section('title', 'Target vs Achievement Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Target vs Achievement</li>
@endsection

@section('content')
    <x-filter-bar :action="route('reports.target-achievement')">
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
            <label class="form-label">Month</label>
            <select name="month" class="form-select">
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" @selected($filters['month'] === $month)>{{ \Illuminate\Support\Carbon::create(2000, $month, 1)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" value="{{ $filters['year'] }}">
        </div>
    </x-filter-bar>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Target vs Achievement</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('reports.target-achievement.export', request()->query()) }}" class="btn btn-outline-secondary"><i class="ti ti-file-spreadsheet me-1"></i>Export</a>
                <a href="{{ route('reports.target-achievement.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank"><i class="ti ti-printer me-1"></i>Print</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Executive</th>
                        <th>Order Target</th>
                        <th>Order Achieved</th>
                        <th>Collection Target</th>
                        <th>Collection Achieved</th>
                        <th>Overall %</th>
                        <th>Grade</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                {{ $row->user?->name }}
                                <div class="text-muted small">{{ $row->user?->territory_names ?? '—' }}</div>
                            </td>
                            <td>{{ number_format($row->order_target, 2) }}</td>
                            <td>{{ number_format($row->order_achieved, 2) }} <span class="text-muted small">({{ $row->order_pct }}%)</span></td>
                            <td>{{ number_format($row->collection_target, 2) }}</td>
                            <td>{{ number_format($row->collection_achieved, 2) }} <span class="text-muted small">({{ $row->collection_pct }}%)</span></td>
                            <td class="fw-semibold">{{ $row->overall_pct }}%</td>
                            <td>
                                @if ($row->grade)
                                    <span class="badge text-bg-{{ $row->grade->badgeColor() }}">{{ $row->grade->label() }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                @can('targets.view')
                                    <a href="{{ route('targets.show', $row->id) }}" class="btn btn-outline-secondary btn-sm" title="View product-wise achievement breakdown">
                                        <i class="ti ti-eye me-1"></i>View
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No targets found for this period.</td>
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
