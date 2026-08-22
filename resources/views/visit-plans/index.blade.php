@extends('layouts.admin')

@section('title', 'Visit Plans')

@section('breadcrumb')
    <li class="breadcrumb-item active">Visit Plans</li>
@endsection

@section('content')
    <x-filter-bar :action="route('visit-plans.index')">
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

    <form id="bulkForm" method="POST" action="{{ route('visit-plans.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected visit plans?">
        @csrf
        <x-data-table
            title="Visit Plans"
            :create-url="auth()->user()->can('create', \App\Models\VisitPlan::class) ? route('visit-plans.create') : null"
            create-label="Plan Visit"
            :export-url="auth()->user()->can('export', \App\Models\VisitPlan::class) ? route('visit-plans.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\VisitPlan::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\VisitPlan::class) ? route('visit-plans.print', request()->query()) : null"
            :paginator="$visitPlans"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Executive</th>
                    <th>Dealer</th>
                    <th>Planned Date</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($visitPlans as $visitPlan)
                <tr>
                    <td>
                        @if (! $visitPlan->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $visitPlan->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        {{ $visitPlan->user?->name }}
                        <div class="text-muted small">{{ $visitPlan->user?->employee_id }}</div>
                        <div class="small"><x-phone-actions :phone="$visitPlan->user?->phone" /></div>
                    </td>
                    <td>
                        @if ($visitPlan->dealer && ! $visitPlan->dealer->trashed())
                            <a href="{{ route('dealers.show', $visitPlan->dealer) }}">{{ $visitPlan->dealer->name }}</a>
                        @else
                            {{ $visitPlan->dealer?->name }}
                        @endif
                        <div class="text-muted small">{{ $visitPlan->dealer?->dealer_code }}</div>
                        <div class="small"><x-phone-actions :phone="$visitPlan->dealer?->phone" /></div>
                    </td>
                    <td>{{ $visitPlan->planned_date->format('M d, Y') }}</td>
                    <td>
                        <span class="badge text-bg-{{ $visitPlan->status->badgeColor() }}">{{ $visitPlan->status->label() }}</span>
                        @if ($visitPlan->isMissed())
                            <span class="badge text-bg-danger">Missed</span>
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($visitPlan->notes, 40) ?: '—' }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($visitPlan->trashed())
                                @can('restore', $visitPlan)
                                    <form method="POST" action="{{ route('visit-plans.restore', $visitPlan->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $visitPlan)
                                    <form method="POST" action="{{ route('visit-plans.force-destroy', $visitPlan->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $visitPlan)
                                    <a href="{{ route('visit-plans.edit', $visitPlan) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $visitPlan)
                                    <form method="POST" action="{{ route('visit-plans.destroy', $visitPlan) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No visit plans found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($visitPlans as $visitPlan)
                    <div class="col">
                        <x-item-card
                            icon="ti-calendar-event"
                            icon-color="{{ $visitPlan->status->badgeColor() }}"
                            :title="$visitPlan->dealer?->name"
                            :title-url="$visitPlan->dealer && ! $visitPlan->dealer->trashed() ? route('dealers.show', $visitPlan->dealer) : null"
                            :subtitle="$visitPlan->dealer?->dealer_code"
                            :status-label="$visitPlan->trashed() ? 'Trashed' : ($visitPlan->isMissed() ? 'Missed' : $visitPlan->status->label())"
                            :status-color="$visitPlan->trashed() ? 'danger' : ($visitPlan->isMissed() ? 'danger' : $visitPlan->status->badgeColor())"
                        >
                            <x-slot:meta>
                                <div>Executive: {{ $visitPlan->user?->name }} &middot; <x-phone-actions :phone="$visitPlan->user?->phone" /></div>
                                <div>Dealer Phone: <x-phone-actions :phone="$visitPlan->dealer?->phone" /></div>
                                <div>Date: {{ $visitPlan->planned_date->format('M d, Y') }}</div>
                                @if ($visitPlan->notes)
                                    <div>{{ \Illuminate\Support\Str::limit($visitPlan->notes, 60) }}</div>
                                @endif
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $visitPlan->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $visitPlan->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($visitPlan->trashed())
                                    @can('restore', $visitPlan)
                                        <form method="POST" action="{{ route('visit-plans.restore', $visitPlan->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $visitPlan)
                                        <form method="POST" action="{{ route('visit-plans.force-destroy', $visitPlan->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $visitPlan)
                                        <a href="{{ route('visit-plans.edit', $visitPlan) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $visitPlan)
                                        <form method="POST" action="{{ route('visit-plans.destroy', $visitPlan) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No visit plans found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('visit-plans.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\VisitPlan::class)
        <x-modal id="importModal" title="Import Visit Plans">
            <form id="importForm" method="POST" action="{{ route('visit-plans.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: employee_id, dealer_code, planned_date, status, notes.</div>
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
