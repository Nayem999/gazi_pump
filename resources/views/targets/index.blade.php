@extends('layouts.admin')

@section('title', 'Targets')

@section('breadcrumb')
    <li class="breadcrumb-item active">Targets</li>
@endsection

@section('content')
    <x-filter-bar :action="route('targets.index')">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Executive name or ID..." value="{{ $filters['search'] ?? '' }}">
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
            <label class="form-label">Month</label>
            <select name="month" class="form-select">
                <option value="">All</option>
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" @selected((string) ($filters['month'] ?? '') === (string) $month)>{{ \Illuminate\Support\Carbon::create(2000, $month, 1)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" placeholder="e.g. {{ now()->year }}" value="{{ $filters['year'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Grade</label>
            <select name="grade" class="form-select">
                <option value="">All</option>
                @foreach (\App\Enums\PerformanceGrade::cases() as $grade)
                    <option value="{{ $grade->value }}" @selected(($filters['grade'] ?? '') === $grade->value)>{{ $grade->value }} — {{ $grade->label() }}</option>
                @endforeach
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('targets.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected targets?">
        @csrf
        <x-data-table
            title="Targets"
            :create-url="auth()->user()->can('create', \App\Models\Target::class) ? route('targets.create') : null"
            create-label="Assign Target"
            :export-url="auth()->user()->can('export', \App\Models\Target::class) ? route('targets.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Target::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Target::class) ? route('targets.print', request()->query()) : null"
            :paginator="$targets"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Executive</th>
                    <th>Period</th>
                    <th>Sales %</th>
                    <th>Collection %</th>
                    <th>Qty %</th>
                    <th>Overall</th>
                    <th>Grade</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($targets as $target)
                <tr>
                    <td>
                        @if (! $target->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $target->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        {{ $target->user?->name }}
                        <div class="text-muted small">{{ $target->user?->employee_id }}</div>
                    </td>
                    <td>{{ $target->periodLabel() }}</td>
                    <td>{{ $target->achievement ? number_format((float) $target->achievement->sales_pct, 1).'%' : '—' }}</td>
                    <td>{{ $target->achievement ? number_format((float) $target->achievement->collection_pct, 1).'%' : '—' }}</td>
                    <td>{{ $target->achievement ? number_format((float) $target->achievement->quantity_pct, 1).'%' : '—' }}</td>
                    <td class="fw-semibold">{{ $target->achievement ? number_format((float) $target->achievement->overall_pct, 1).'%' : '—' }}</td>
                    <td>
                        @if ($target->achievement)
                            <span class="badge text-bg-{{ $target->achievement->grade->badgeColor() }}">{{ $target->achievement->grade->value }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($target->trashed())
                                @can('restore', $target)
                                    <form method="POST" action="{{ route('targets.restore', $target->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $target)
                                    <form method="POST" action="{{ route('targets.force-destroy', $target->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('view', $target)
                                    <a href="{{ route('targets.show', $target) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                @endcan
                                @can('update', $target)
                                    <a href="{{ route('targets.edit', $target) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    <form method="POST" action="{{ route('targets.recalculate', $target) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info" title="Recalculate"><i class="ti ti-refresh"></i></button>
                                    </form>
                                @endcan
                                @can('delete', $target)
                                    <form method="POST" action="{{ route('targets.destroy', $target) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <td colspan="9" class="text-center text-muted py-4">No targets found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($targets as $target)
                    <div class="col">
                        <x-item-card
                            icon="ti-target-arrow"
                            :icon-color="$target->achievement?->grade->badgeColor() ?? 'secondary'"
                            :title="$target->user?->name"
                            :subtitle="$target->periodLabel()"
                            :status-label="$target->trashed() ? 'Trashed' : $target->achievement?->grade->value"
                            :status-color="$target->trashed() ? 'danger' : ($target->achievement?->grade->badgeColor() ?? 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Sales: {{ $target->achievement ? number_format((float) $target->achievement->sales_pct, 1).'%' : '—' }}</div>
                                <div>Collection: {{ $target->achievement ? number_format((float) $target->achievement->collection_pct, 1).'%' : '—' }}</div>
                                <div>Qty: {{ $target->achievement ? number_format((float) $target->achievement->quantity_pct, 1).'%' : '—' }}</div>
                                <div>Overall: {{ $target->achievement ? number_format((float) $target->achievement->overall_pct, 1).'%' : '—' }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $target->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $target->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($target->trashed())
                                    @can('restore', $target)
                                        <form method="POST" action="{{ route('targets.restore', $target->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $target)
                                        <form method="POST" action="{{ route('targets.force-destroy', $target->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('view', $target)
                                        <a href="{{ route('targets.show', $target) }}" class="btn btn-outline-secondary" title="View"><i class="ti ti-eye"></i></a>
                                    @endcan
                                    @can('update', $target)
                                        <a href="{{ route('targets.edit', $target) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $target)
                                        <form method="POST" action="{{ route('targets.destroy', $target) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No targets found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('targets.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Target::class)
        <x-modal id="importModal" title="Import Targets">
            <form id="importForm" method="POST" action="{{ route('targets.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: employee_id, month, year, sales_value_target, collection_target, quantity_target, notes.</div>
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
