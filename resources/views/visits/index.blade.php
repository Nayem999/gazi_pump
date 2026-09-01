@extends('layouts.admin')

@section('title', 'Dealer Visits')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dealer Visits</li>
@endsection

@section('content')
    <x-filter-bar :action="route('visits.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Dealer name or code..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Executive</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach ($executives as $executive)
                    <option value="{{ $executive->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $executive->id)>{{ $executive->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('visits.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected visits?">
        @csrf
        <x-data-table
            title="Dealer Visits"
            :create-url="auth()->user()->can('create', \App\Models\Visit::class) ? route('visits.create') : null"
            create-label="Record Visit"
            :export-url="auth()->user()->can('export', \App\Models\Visit::class) ? route('visits.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Visit::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Visit::class) ? route('visits.print', request()->query()) : null"
            :paginator="$visits"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Executive</th>
                    <th>Dealer</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>GPS Verified</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($visits as $visit)
                <tr>
                    <td>
                        @if (! $visit->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $visit->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        {{ $visit->user?->name }}
                        <div class="text-muted small">{{ $visit->user?->employee_id }}</div>
                        <div class="small"><x-phone-actions :phone="$visit->user?->phone" /></div>
                    </td>
                    <td>
                        @if ($visit->dealer && ! $visit->dealer->trashed())
                            <a href="{{ route('dealers.show', $visit->dealer) }}">{{ $visit->dealer->name }}</a>
                        @else
                            {{ $visit->dealer?->name }}
                        @endif
                        <div class="text-muted small">{{ $visit->dealer?->dealer_code }}</div>
                        <div class="small"><x-phone-actions :phone="$visit->dealer?->phone" /></div>
                    </td>
                    <td>{{ $visit->check_in_at?->format('d M Y, h:i A') }}</td>
                    <td>{{ $visit->check_out_at?->format('d M Y, h:i A') ?? '—' }}</td>
                    <td>
                        <span class="badge text-bg-{{ match ($visit->is_gps_verified) { true => 'success', false => 'danger', default => 'secondary' } }}">
                            {{ match ($visit->is_gps_verified) { true => 'Verified', false => 'Unverified', default => 'Unknown' } }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($visit->trashed())
                                @can('restore', $visit)
                                    <form method="POST" action="{{ route('visits.restore', $visit->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $visit)
                                    <form method="POST" action="{{ route('visits.force-destroy', $visit->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $visit)
                                    <a href="{{ route('visits.show', $visit) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $visit)
                                    <a href="{{ route('visits.edit', $visit) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $visit)
                                    <form method="POST" action="{{ route('visits.destroy', $visit) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No visits found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($visits as $visit)
                    <div class="col">
                        <x-item-card
                            icon="ti-walk"
                            icon-color="{{ match ($visit->is_gps_verified) { true => 'success', false => 'danger', default => 'secondary' } }}"
                            :title="$visit->dealer?->name"
                            :title-url="$visit->dealer && ! $visit->dealer->trashed() ? route('dealers.show', $visit->dealer) : null"
                            :subtitle="$visit->user?->name"
                            :status-label="$visit->trashed() ? 'Trashed' : match ($visit->is_gps_verified) { true => 'Verified', false => 'Unverified', default => 'Unknown' }"
                            :status-color="$visit->trashed() ? 'danger' : match ($visit->is_gps_verified) { true => 'success', false => 'danger', default => 'secondary' }"
                        >
                            <x-slot:meta>
                                <div>Executive: {{ $visit->user?->name }} &middot; <x-phone-actions :phone="$visit->user?->phone" /></div>
                                <div>Dealer Phone: <x-phone-actions :phone="$visit->dealer?->phone" /></div>
                                <div>In: {{ $visit->check_in_at?->format('d M Y, h:i A') }}</div>
                                <div>Out: {{ $visit->check_out_at?->format('d M Y, h:i A') ?? '—' }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $visit->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $visit->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($visit->trashed())
                                    @can('restore', $visit)
                                        <form method="POST" action="{{ route('visits.restore', $visit->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $visit)
                                        <form method="POST" action="{{ route('visits.force-destroy', $visit->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $visit)
                                        <a href="{{ route('visits.show', $visit) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $visit)
                                        <a href="{{ route('visits.edit', $visit) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $visit)
                                        <form method="POST" action="{{ route('visits.destroy', $visit) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No visits found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('visits.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Visit::class)
        <x-modal id="importModal" title="Import Visits">
            <form id="importForm" method="POST" action="{{ route('visits.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: employee_id, dealer_code, check_in_at, check_out_at, feedback.</div>
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
    </script>
@endpush
