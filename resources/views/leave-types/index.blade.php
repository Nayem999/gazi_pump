@extends('layouts.admin')

@section('title', 'Leave Types')

@section('breadcrumb')
    <li class="breadcrumb-item active">Leave Types</li>
@endsection

@section('content')
    <x-filter-bar :action="route('leave-types.index')">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or code..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    {{-- Deliberately empty and self-closed, NOT wrapping the table. HTML
         forbids nested forms: while this form wrapped the rows, the parser
         discarded each row's own <form> start tag and then let the FIRST
         row's </form> close this one - so row 1's delete button submitted
         the bulk-destroy form (404) while every later row worked. The
         checkboxes and the button below join it by id via the HTML5 form
         attribute, which needs no nesting. --}}
    <form id="bulkForm" method="POST" action="{{ route('leave-types.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected leave types?">
        @csrf
    </form>
        <x-data-table
            title="Leave Types"
            :create-url="auth()->user()->can('create', \App\Models\LeaveType::class) ? route('leave-types.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\LeaveType::class) ? route('leave-types.export', request()->query()) : null"
            :print-url="auth()->user()->can('print', \App\Models\LeaveType::class) ? route('leave-types.print', request()->query()) : null"
            :paginator="$leaveTypes"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Annual Quota</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($leaveTypes as $leaveType)
                <tr>
                    <td>
                        @if (! $leaveType->trashed())
                            <input type="checkbox" name="ids[]" form="bulkForm" value="{{ $leaveType->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $leaveType->name }}</td>
                    <td><span class="badge text-bg-light text-dark">{{ $leaveType->code }}</span></td>
                    <td>{{ $leaveType->annual_quota }} day(s)</td>
                    <td>
                        <span class="badge text-bg-{{ $leaveType->is_paid ? 'info' : 'secondary' }}">
                            {{ $leaveType->is_paid ? 'Paid' : 'Unpaid' }}
                        </span>
                    </td>
                    <td>
                        @if ($leaveType->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $leaveType->status ? 'success' : 'secondary' }}">
                                {{ $leaveType->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($leaveType->trashed())
                                @can('restore', $leaveType)
                                    <form method="POST" action="{{ route('leave-types.restore', $leaveType->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $leaveType)
                                    <form method="POST" action="{{ route('leave-types.force-destroy', $leaveType->id) }}" data-confirm data-confirm-title="Permanently delete this leave type?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $leaveType)
                                    <a href="{{ route('leave-types.edit', $leaveType) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $leaveType)
                                    <form method="POST" action="{{ route('leave-types.destroy', $leaveType) }}" data-confirm data-confirm-title="Move this leave type to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No leave types found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($leaveTypes as $leaveType)
                    <div class="col">
                        <x-item-card
                            icon="ti-beach"
                            icon-color="primary"
                            :title="$leaveType->name"
                            :subtitle="$leaveType->code"
                            :status-label="$leaveType->trashed() ? 'Trashed' : ($leaveType->status ? 'Active' : 'Inactive')"
                            :status-color="$leaveType->trashed() ? 'danger' : ($leaveType->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>{{ $leaveType->annual_quota }} day(s) a year - {{ $leaveType->is_paid ? 'paid' : 'unpaid' }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $leaveType->trashed())
                                    <input type="checkbox" name="ids[]" form="bulkForm" value="{{ $leaveType->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($leaveType->trashed())
                                    @can('restore', $leaveType)
                                        <form method="POST" action="{{ route('leave-types.restore', $leaveType->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $leaveType)
                                        <form method="POST" action="{{ route('leave-types.force-destroy', $leaveType->id) }}" data-confirm data-confirm-title="Permanently delete this leave type?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $leaveType)
                                        <a href="{{ route('leave-types.edit', $leaveType) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $leaveType)
                                        <form method="POST" action="{{ route('leave-types.destroy', $leaveType) }}" data-confirm data-confirm-title="Move this leave type to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No leave types found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('leave-types.delete')
            <div class="mt-2">
                <button type="submit" form="bulkForm" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan

    @can('import', \App\Models\LeaveType::class)
        <x-modal id="importModal" title="Import Leave Types">
            <form id="importForm" method="POST" action="{{ route('leave-types.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: name, code, annual_quota, is_paid, description.</div>
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
