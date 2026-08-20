@extends('layouts.admin')

@section('title', 'Service Centers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Service Centers</li>
@endsection

@section('content')
    <x-filter-bar :action="route('service-centers.index')">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Trashed</label>
            <select name="trashed" class="form-select">
                <option value="">Without Trashed</option>
                <option value="with" @selected(($filters['trashed'] ?? '') === 'with')>With Trashed</option>
                <option value="only" @selected(($filters['trashed'] ?? '') === 'only')>Only Trashed</option>
            </select>
        </div>
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('service-centers.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected service centers?">
        @csrf
        <x-data-table
            title="Service Centers"
            :create-url="auth()->user()->can('create', \App\Models\ServiceCenter::class) ? route('service-centers.create') : null"
            :paginator="$serviceCenters"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>GPS</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($serviceCenters as $serviceCenter)
                <tr>
                    <td>
                        @if (! $serviceCenter->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $serviceCenter->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $serviceCenter->name }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($serviceCenter->address, 40) }}</td>
                    <td>{{ $serviceCenter->phone }}</td>
                    <td>
                        @if ($serviceCenter->hasGps())
                            <i class="ti ti-map-pin text-success"></i>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($serviceCenter->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $serviceCenter->is_active ? 'success' : 'secondary' }}">
                                {{ $serviceCenter->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($serviceCenter->trashed())
                                @can('restore', $serviceCenter)
                                    <form method="POST" action="{{ route('service-centers.restore', $serviceCenter->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $serviceCenter)
                                    <form method="POST" action="{{ route('service-centers.force-destroy', $serviceCenter->id) }}" data-confirm data-confirm-title="Permanently delete this service center?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $serviceCenter)
                                    <a href="{{ route('service-centers.edit', $serviceCenter) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $serviceCenter)
                                    <form method="POST" action="{{ route('service-centers.destroy', $serviceCenter) }}" data-confirm data-confirm-title="Move this service center to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No service centers found.</td>
                </tr>
            @endforelse
        </x-data-table>

        @can('service-centers.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('selectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
        });
    </script>
@endpush
