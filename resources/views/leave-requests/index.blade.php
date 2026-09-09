@extends('layouts.admin')

@section('title', 'Leave Requests')

@section('breadcrumb')
    <li class="breadcrumb-item active">Leave Requests</li>
@endsection

@section('content')
    <x-filter-bar :action="route('leave-requests.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Employee or reason..." value="{{ $filters['search'] ?? '' }}">
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
            <label class="form-label">Leave Type</label>
            <select name="leave_type_id" class="form-select">
                <option value="">All</option>
                @foreach ($leaveTypes as $type)
                    <option value="{{ $type->id }}" @selected(($filters['leave_type_id'] ?? '') == $type->id)>{{ $type->name }}</option>
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

    {{-- Empty and self-closed, NOT wrapping the table: HTML forbids nested
         forms, and a bulk form around the rows makes the first row's
         delete button submit the wrong form. The controls join it by id
         with the HTML5 form attribute instead. --}}
    <form id="bulkForm" method="POST" action="{{ route('leave-requests.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected leave requests?">
        @csrf
    </form>

    <x-data-table
        title="Leave Requests"
        :create-url="auth()->user()->can('create', \App\Models\LeaveRequest::class) ? route('leave-requests.create') : null"
        :export-url="auth()->user()->can('export', \App\Models\LeaveRequest::class) ? route('leave-requests.export', request()->query()) : null"
        :print-url="auth()->user()->can('print', \App\Models\LeaveRequest::class) ? route('leave-requests.print', request()->query()) : null"
        :paginator="$leaveRequests"
    >
        <x-slot:thead>
            <tr>
                <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                <th>Employee</th>
                <th>Type</th>
                <th>Dates</th>
                <th>Days</th>
                <th>Status</th>
                <th>Decided By</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @forelse ($leaveRequests as $leaveRequest)
            <tr>
                <td>
                    @if (! $leaveRequest->trashed())
                        <input type="checkbox" name="ids[]" form="bulkForm" value="{{ $leaveRequest->id }}" class="form-check-input row-checkbox">
                    @endif
                </td>
                <td>
                    {{ $leaveRequest->user?->name }}
                    <div class="text-muted small">{{ $leaveRequest->user?->employee_id }}</div>
                </td>
                <td>{{ $leaveRequest->leaveType?->name }}</td>
                <td>
                    {{ $leaveRequest->from_date->format('d M Y') }}
                    @unless ($leaveRequest->from_date->isSameDay($leaveRequest->to_date))
                        &rarr; {{ $leaveRequest->to_date->format('d M Y') }}
                    @endunless
                </td>
                <td>
                    {{ rtrim(rtrim(number_format((float) $leaveRequest->days, 1), '0'), '.') }}
                    @if ($leaveRequest->is_half_day) <span class="badge text-bg-info">½</span> @endif
                </td>
                <td>
                    @if ($leaveRequest->trashed())
                        <span class="badge text-bg-danger">Trashed</span>
                    @else
                        <span class="badge text-bg-{{ $leaveRequest->status->badgeColor() }}">{{ $leaveRequest->status->label() }}</span>
                    @endif
                </td>
                <td>{{ $leaveRequest->approver?->name ?? '—' }}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        @if ($leaveRequest->trashed())
                            @can('restore', $leaveRequest)
                                <form method="POST" action="{{ route('leave-requests.restore', $leaveRequest->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                </form>
                            @endcan
                            @can('forceDelete', $leaveRequest)
                                <form method="POST" action="{{ route('leave-requests.force-destroy', $leaveRequest->id) }}" data-confirm data-confirm-title="Permanently delete this request?" data-confirm-text="This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                </form>
                            @endcan
                        @else
                            @can('view', $leaveRequest)
                                <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                            @endcan
                            @can('update', $leaveRequest)
                                <a href="{{ route('leave-requests.edit', $leaveRequest) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                            @endcan
                            @can('delete', $leaveRequest)
                                <form method="POST" action="{{ route('leave-requests.destroy', $leaveRequest) }}" data-confirm data-confirm-title="Move this request to trash?">
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
                <td colspan="8" class="text-center text-muted py-4">No leave requests found.</td>
            </tr>
        @endforelse

        <x-slot:cards>
            @forelse ($leaveRequests as $leaveRequest)
                <div class="col">
                    <x-item-card
                        icon="ti-calendar-off"
                        icon-color="primary"
                        :title="$leaveRequest->user?->name ?? 'Unknown'"
                        :subtitle="$leaveRequest->leaveType?->name"
                        :status-label="$leaveRequest->trashed() ? 'Trashed' : $leaveRequest->status->label()"
                        :status-color="$leaveRequest->trashed() ? 'danger' : $leaveRequest->status->badgeColor()"
                    >
                        <x-slot:meta>
                            <div>
                                {{ $leaveRequest->from_date->format('d M Y') }}
                                @unless ($leaveRequest->from_date->isSameDay($leaveRequest->to_date))
                                    &rarr; {{ $leaveRequest->to_date->format('d M Y') }}
                                @endunless
                                &middot; {{ rtrim(rtrim(number_format((float) $leaveRequest->days, 1), '0'), '.') }} day(s)
                            </div>
                        </x-slot:meta>
                        <x-slot:checkbox>
                            @if (! $leaveRequest->trashed())
                                <input type="checkbox" name="ids[]" form="bulkForm" value="{{ $leaveRequest->id }}" class="form-check-input row-checkbox">
                            @endif
                        </x-slot:checkbox>
                        <x-slot:actions>
                            @if ($leaveRequest->trashed())
                                @can('restore', $leaveRequest)
                                    <form method="POST" action="{{ route('leave-requests.restore', $leaveRequest->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $leaveRequest)
                                    <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('delete', $leaveRequest)
                                    <form method="POST" action="{{ route('leave-requests.destroy', $leaveRequest) }}" data-confirm data-confirm-title="Move this request to trash?">
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
                <div class="col-12 text-center text-muted py-4">No leave requests found.</div>
            @endforelse
        </x-slot:cards>
    </x-data-table>

    <div class="mt-2 d-flex gap-2">
        @can('leave-requests.delete')
            <button type="submit" form="bulkForm" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
        @endcan
        <a href="{{ route('leave-requests.balances') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-scale me-1"></i>Leave Balances
        </a>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('selectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
        });
    </script>
@endpush
