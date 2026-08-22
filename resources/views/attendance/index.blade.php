@extends('layouts.admin')

@section('title', 'Attendance')

@section('breadcrumb')
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')
    <x-filter-bar :action="route('attendance.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Employee name or ID..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
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
        @include('partials.trashed-filter', ['filters' => $filters])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('attendance.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected attendance records?">
        @csrf
        <x-data-table
            title="Attendance"
            :create-url="auth()->user()->can('create', \App\Models\Attendance::class) ? route('attendance.create') : null"
            create-label="Record Attendance"
            :export-url="auth()->user()->can('export', \App\Models\Attendance::class) ? route('attendance.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Attendance::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Attendance::class) ? route('attendance.print', request()->query()) : null"
            :paginator="$attendances"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Late (min)</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($attendances as $attendance)
                <tr>
                    <td>
                        @if (! $attendance->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $attendance->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        {{ $attendance->user?->name }}
                        <div class="text-muted small">{{ $attendance->user?->employee_id }}</div>
                        <div class="small"><x-phone-actions :phone="$attendance->user?->phone" /></div>
                    </td>
                    <td>{{ $attendance->date->format('M d, Y') }}</td>
                    <td>{{ $attendance->check_in_at?->format('H:i') ?? '—' }}</td>
                    <td>{{ $attendance->check_out_at?->format('H:i') ?? '—' }}</td>
                    <td><span class="badge text-bg-{{ $attendance->status->badgeColor() }}">{{ $attendance->status->label() }}</span></td>
                    <td>{{ $attendance->late_minutes ?: '—' }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($attendance->trashed())
                                @can('restore', $attendance)
                                    <form method="POST" action="{{ route('attendance.restore', $attendance->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $attendance)
                                    <form method="POST" action="{{ route('attendance.force-destroy', $attendance->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $attendance)
                                    <a href="{{ route('attendance.show', $attendance) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $attendance)
                                    <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $attendance)
                                    <form method="POST" action="{{ route('attendance.destroy', $attendance) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <td colspan="8" class="text-center text-muted py-4">No attendance records found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($attendances as $attendance)
                    <div class="col">
                        <x-item-card
                            icon="ti-calendar-check"
                            icon-color="{{ $attendance->status->badgeColor() }}"
                            :image="$attendance->user?->photo ? $attendance->user->photoUrl() : null"
                            :title="$attendance->user?->name"
                            :subtitle="$attendance->user?->employee_id"
                            :status-label="$attendance->trashed() ? 'Trashed' : $attendance->status->label()"
                            :status-color="$attendance->trashed() ? 'danger' : $attendance->status->badgeColor()"
                        >
                            <x-slot:meta>
                                <div>Phone: <x-phone-actions :phone="$attendance->user?->phone" /></div>
                                <div>Date: {{ $attendance->date->format('M d, Y') }}</div>
                                <div>In: {{ $attendance->check_in_at?->format('H:i') ?? '—' }} &middot; Out: {{ $attendance->check_out_at?->format('H:i') ?? '—' }}</div>
                                @if ($attendance->late_minutes)
                                    <div>Late: {{ $attendance->late_minutes }} min</div>
                                @endif
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $attendance->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $attendance->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($attendance->trashed())
                                    @can('restore', $attendance)
                                        <form method="POST" action="{{ route('attendance.restore', $attendance->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $attendance)
                                        <form method="POST" action="{{ route('attendance.force-destroy', $attendance->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $attendance)
                                        <a href="{{ route('attendance.show', $attendance) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $attendance)
                                        <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $attendance)
                                        <form method="POST" action="{{ route('attendance.destroy', $attendance) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No attendance records found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('attendance.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Attendance::class)
        <x-modal id="importModal" title="Import Attendance">
            <form id="importForm" method="POST" action="{{ route('attendance.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: employee_id, date, status, check_in_time, check_out_time, remarks.</div>
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
