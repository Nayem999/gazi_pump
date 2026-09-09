@extends('layouts.admin')

@section('title', 'Leave Entitlements')

@section('breadcrumb')
    <li class="breadcrumb-item active">Leave Entitlements</li>
@endsection

@section('content')
    @can('create', \App\Models\LeaveBalance::class)
        <div class="card mb-4">
            <div class="card-body">
                <div class="fw-semibold">Set up entitlements for a year</div>
                <div class="small text-muted mb-3">
                    Gives every active employee an entitlement for each active leave type, taken from that type's
                    annual quota. Safe to run more than once: anyone who already has an entitlement is left exactly
                    as they are, including entitlements you have adjusted by hand or deleted on purpose.
                </div>

                <form method="POST" action="{{ route('leave-balances.set-up') }}" class="row g-2 align-items-end"
                      data-confirm data-confirm-title="Set up entitlements?"
                      data-confirm-text="Existing entitlements are never overwritten.">
                    @csrf
                    <div class="col-auto">
                        <label class="form-label small">Year</label>
                        <input type="number" name="year" class="form-control" value="{{ $year }}" min="2000" max="2100" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-wand me-1"></i>Set Up Entitlements
                        </button>
                    </div>
                    <div class="col-12">
                        <div class="form-text">
                            Days already taken are never set here &mdash; those are counted from approved leave
                            requests, so this screen only decides how many days people are granted.
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <x-filter-bar :action="route('leave-balances.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or employee ID..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" value="{{ $filters['year'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Employee</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>
                        {{ $user->name }} @if ($user->employee_id) ({{ $user->employee_id }}) @endif
                    </option>
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
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-2'])
    </x-filter-bar>

    {{-- Empty and self-closed, NOT wrapping the table: HTML forbids nested
         forms, and a bulk form around the rows makes the first row's
         delete button submit the wrong one. The controls join it by id. --}}
    <form id="bulkForm" method="POST" action="{{ route('leave-balances.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected entitlements?">
        @csrf
    </form>

    <x-data-table
        title="Entitlements"
        :create-url="auth()->user()->can('create', \App\Models\LeaveBalance::class) ? route('leave-balances.create', ['year' => $year]) : null"
        :export-url="auth()->user()->can('export', \App\Models\LeaveBalance::class) ? route('leave-balances.export', request()->query()) : null"
        :print-url="auth()->user()->can('print', \App\Models\LeaveBalance::class) ? route('leave-balances.print', request()->query()) : null"
        :paginator="$leaveBalances"
    >
        <x-slot:thead>
            <tr>
                <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                <th>Employee</th>
                <th>Year</th>
                <th>Leave Type</th>
                <th class="text-end">Entitled</th>
                <th class="text-end">Carried Forward</th>
                <th class="text-end">Total</th>
                <th>Remarks</th>
                <th class="text-end">Actions</th>
            </tr>
        </x-slot:thead>

        @php($fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.'))

        @forelse ($leaveBalances as $leaveBalance)
            <tr>
                <td>
                    @if (! $leaveBalance->trashed())
                        <input type="checkbox" name="ids[]" form="bulkForm" value="{{ $leaveBalance->id }}" class="form-check-input row-checkbox">
                    @endif
                </td>
                <td>
                    {{ $leaveBalance->user?->name }}
                    <div class="text-muted small">{{ $leaveBalance->user?->employee_id }}</div>
                </td>
                <td>{{ $leaveBalance->year }}</td>
                <td>
                    {{ $leaveBalance->leaveType?->name }}
                    @if ($leaveBalance->trashed())
                        <span class="badge text-bg-danger">Trashed</span>
                    @endif
                </td>
                <td class="text-end">{{ $fmt($leaveBalance->entitled_days) }}</td>
                <td class="text-end">{{ $fmt($leaveBalance->carried_forward_days) }}</td>
                <td class="text-end fw-semibold">{{ $fmt($leaveBalance->totalEntitlement()) }}</td>
                <td class="small text-muted">{{ $leaveBalance->remarks }}</td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        @if ($leaveBalance->trashed())
                            @can('restore', $leaveBalance)
                                <form method="POST" action="{{ route('leave-balances.restore', $leaveBalance->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                </form>
                            @endcan
                            @can('forceDelete', $leaveBalance)
                                <form method="POST" action="{{ route('leave-balances.force-destroy', $leaveBalance->id) }}" data-confirm data-confirm-title="Permanently delete this entitlement?" data-confirm-text="This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                </form>
                            @endcan
                        @else
                            @can('update', $leaveBalance)
                                <a href="{{ route('leave-balances.edit', $leaveBalance) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                            @endcan
                            @can('delete', $leaveBalance)
                                <form method="POST" action="{{ route('leave-balances.destroy', $leaveBalance) }}"
                                      data-confirm data-confirm-title="Delete this entitlement?"
                                      data-confirm-text="That person falls back to the leave type's default quota.">
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
                <td colspan="9" class="text-center text-muted py-4">
                    No entitlements for {{ $filters['year'] ?? 'this filter' }} yet.
                    @can('create', \App\Models\LeaveBalance::class)
                        Use <strong>Set Up Entitlements</strong> above to create them from each leave type's quota.
                    @endcan
                </td>
            </tr>
        @endforelse

        <x-slot:cards>
            @forelse ($leaveBalances as $leaveBalance)
                <div class="col">
                    <x-item-card
                        icon="ti-scale"
                        icon-color="primary"
                        :title="$leaveBalance->user?->name ?? 'Unknown'"
                        :subtitle="$leaveBalance->leaveType?->name.' · '.$leaveBalance->year"
                        :status-label="$leaveBalance->trashed() ? 'Trashed' : $fmt($leaveBalance->totalEntitlement()).' day(s)'"
                        :status-color="$leaveBalance->trashed() ? 'danger' : 'primary'"
                    >
                        <x-slot:meta>
                            <div>
                                Entitled {{ $fmt($leaveBalance->entitled_days) }}
                                @if ((float) $leaveBalance->carried_forward_days > 0)
                                    &middot; carried forward {{ $fmt($leaveBalance->carried_forward_days) }}
                                @endif
                            </div>
                        </x-slot:meta>
                        <x-slot:checkbox>
                            @if (! $leaveBalance->trashed())
                                <input type="checkbox" name="ids[]" form="bulkForm" value="{{ $leaveBalance->id }}" class="form-check-input row-checkbox">
                            @endif
                        </x-slot:checkbox>
                        <x-slot:actions>
                            @if ($leaveBalance->trashed())
                                @can('restore', $leaveBalance)
                                    <form method="POST" action="{{ route('leave-balances.restore', $leaveBalance->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $leaveBalance)
                                    <a href="{{ route('leave-balances.edit', $leaveBalance) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                            @endif
                        </x-slot:actions>
                    </x-item-card>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">No entitlements found.</div>
            @endforelse
        </x-slot:cards>
    </x-data-table>

    <div class="mt-2 d-flex gap-2">
        @can('leave-balances.delete')
            <button type="submit" form="bulkForm" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
        @endcan
        <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-calendar-off me-1"></i>Leave Requests
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
