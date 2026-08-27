@extends('layouts.admin')

@section('title', 'Dealers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dealers</li>
@endsection

@section('content')
    <x-filter-bar :action="route('dealers.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name, code, phone..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Division</label>
            <select name="division_id" id="filterDivision" class="form-select">
                <option value="">All Divisions</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}" @selected((string) ($filters['division_id'] ?? '') === (string) $division->id)>{{ $division->name }}</option>
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
        <div class="col-md-2">
            <label class="form-label">Territory</label>
            <select name="territory_id" id="filterTerritory" class="form-select" @disabled(empty($filters['thana_id']))>
                <option value="">All Territories</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('dealers.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected dealers?">
        @csrf
        <x-data-table
            title="All Dealers"
            :create-url="auth()->user()->can('create', \App\Models\Dealer::class) ? route('dealers.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Dealer::class) ? route('dealers.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Dealer::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Dealer::class) ? route('dealers.print', request()->query()) : null"
            :paginator="$dealers"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Photo</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Division</th>
                    <th>District</th>
                    <th>Thana</th>
                    <th>Territory</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($dealers as $dealer)
                <tr>
                    <td>
                        @if (! $dealer->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $dealer->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        @if ($dealer->image)
                            <img src="{{ $dealer->imageUrl() }}" style="width:36px;height:36px;object-fit:cover" class="rounded">
                        @else
                            <i class="ti ti-building-store text-secondary fs-5"></i>
                        @endif
                    </td>
                    <td>{{ $dealer->dealer_code }}</td>
                    <td>
                        @if ($dealer->trashed())
                            {{ $dealer->name }}
                        @else
                            <a href="{{ route('dealers.show', $dealer) }}">{{ $dealer->name }}</a>
                        @endif
                    </td>
                    <td><x-phone-actions :phone="$dealer->phone" /></td>
                    <td>{{ $dealer->division?->name ?? '—' }}</td>
                    <td>{{ $dealer->district?->name ?? '—' }}</td>
                    <td>{{ $dealer->thana?->name ?? '—' }}</td>
                    <td>{{ $dealer->territory?->name ?? '—' }}</td>
                    <td>
                        @if ($dealer->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $dealer->status ? 'success' : 'secondary' }}">
                                {{ $dealer->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($dealer->trashed())
                                @can('restore', $dealer)
                                    <form method="POST" action="{{ route('dealers.restore', $dealer->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $dealer)
                                    <form method="POST" action="{{ route('dealers.force-destroy', $dealer->id) }}" data-confirm data-confirm-title="Permanently delete this dealer?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $dealer)
                                    <a href="{{ route('dealers.show', $dealer) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $dealer)
                                    <a href="{{ route('dealers.edit', $dealer) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $dealer)
                                    <form method="POST" action="{{ route('dealers.destroy', $dealer) }}" data-confirm data-confirm-title="Move this dealer to trash?">
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
                    <td colspan="11" class="text-center text-muted py-4">No dealers found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($dealers as $dealer)
                    <div class="col">
                        <x-item-card
                            icon="ti-building-store"
                            icon-color="info"
                            :image="$dealer->image ? $dealer->imageUrl() : null"
                            :title="$dealer->name"
                            :title-url="$dealer->trashed() ? null : route('dealers.show', $dealer)"
                            :subtitle="$dealer->dealer_code"
                            :status-label="$dealer->trashed() ? 'Trashed' : ($dealer->status ? 'Active' : 'Inactive')"
                            :status-color="$dealer->trashed() ? 'danger' : ($dealer->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Phone: <x-phone-actions :phone="$dealer->phone" /></div>
                                <div>{{ $dealer->division?->name ?? '—' }} / {{ $dealer->district?->name ?? '—' }} / {{ $dealer->thana?->name ?? '—' }}</div>
                                <div>Territory: {{ $dealer->territory?->name ?? '—' }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $dealer->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $dealer->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($dealer->trashed())
                                    @can('restore', $dealer)
                                        <form method="POST" action="{{ route('dealers.restore', $dealer->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $dealer)
                                        <form method="POST" action="{{ route('dealers.force-destroy', $dealer->id) }}" data-confirm data-confirm-title="Permanently delete this dealer?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $dealer)
                                        <a href="{{ route('dealers.show', $dealer) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $dealer)
                                        <a href="{{ route('dealers.edit', $dealer) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $dealer)
                                        <form method="POST" action="{{ route('dealers.destroy', $dealer) }}" data-confirm data-confirm-title="Move this dealer to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No dealers found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('dealers.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Dealer::class)
        <x-modal id="importModal" title="Import Dealers">
            <form id="importForm" method="POST" action="{{ route('dealers.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: dealer_code, name, phone, email, address, territory_code.</div>
                </div>
            </form>
            <x-slot:footer>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="importForm" class="btn btn-primary">Import</button>
            </x-slot:footer>
        </x-modal>
    @endcan
@endsection

@push('scripts')
    <script>
        document.getElementById('selectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const filterDivision = document.getElementById('filterDivision');
            const filterDistrict = document.getElementById('filterDistrict');
            const filterThana = document.getElementById('filterThana');
            const filterTerritory = document.getElementById('filterTerritory');

            initCascadingSelect(filterDivision, filterDistrict, '{{ route('districts.options') }}', 'division_id', {
                placeholder: 'All Districts',
                initialChildValue: '{{ $filters['district_id'] ?? '' }}',
            });
            initCascadingSelect(filterDistrict, filterThana, '{{ route('thanas.options') }}', 'district_id', {
                placeholder: 'All Thanas',
                initialChildValue: '{{ $filters['thana_id'] ?? '' }}',
            });
            initCascadingSelect(filterThana, filterTerritory, '{{ route('territories.options') }}', 'thana_id', {
                placeholder: 'All Territories',
                initialChildValue: '{{ $filters['territory_id'] ?? '' }}',
            });
        });
    </script>
@endpush
