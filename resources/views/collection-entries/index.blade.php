@extends('layouts.admin')

@section('title', 'Collection Entry')

@section('breadcrumb')
    <li class="breadcrumb-item active">Collection Entry</li>
@endsection

@section('content')
    <x-filter-bar :action="route('collection-entries.index')">
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
            <label class="form-label">Payment Method</label>
            <select name="payment_method" class="form-select">
                <option value="">All</option>
                @foreach ($paymentMethods as $method)
                    <option value="{{ $method->value }}" @selected(($filters['payment_method'] ?? '') === $method->value)>{{ $method->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('collection-entries.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected collections?">
        @csrf
        <x-data-table
            title="Collection Entries"
            :create-url="auth()->user()->can('create', \App\Models\CollectionEntry::class) ? route('collection-entries.create') : null"
            create-label="Record Collection"
            :export-url="auth()->user()->can('export', \App\Models\CollectionEntry::class) ? route('collection-entries.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\CollectionEntry::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\CollectionEntry::class) ? route('collection-entries.print', request()->query()) : null"
            :paginator="$collectionEntries"
            :total="$total"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Executive</th>
                    <th>Dealer</th>
                    <th>Collection Date</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($collectionEntries as $collectionEntry)
                <tr>
                    <td>
                        @if (! $collectionEntry->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $collectionEntry->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        {{ $collectionEntry->user?->name }}
                        <div class="text-muted small">{{ $collectionEntry->user?->employee_id }}</div>
                    </td>
                    <td>
                        @if ($collectionEntry->dealer && ! $collectionEntry->dealer->trashed())
                            <a href="{{ route('dealers.show', $collectionEntry->dealer) }}">{{ $collectionEntry->dealer->name }}</a>
                        @else
                            {{ $collectionEntry->dealer?->name }}
                        @endif
                        <div class="text-muted small">{{ $collectionEntry->dealer?->dealer_code }}</div>
                    </td>
                    <td>{{ $collectionEntry->collection_date->format('M d, Y') }}</td>
                    <td>{{ number_format((float) $collectionEntry->amount, 2) }}</td>
                    <td>
                        <span class="badge text-bg-secondary">{{ $collectionEntry->payment_method->label() }}</span>
                        @if ($collectionEntry->reference_no)
                            <div class="text-muted small">{{ $collectionEntry->reference_no }}</div>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($collectionEntry->trashed())
                                @can('restore', $collectionEntry)
                                    <form method="POST" action="{{ route('collection-entries.restore', $collectionEntry->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $collectionEntry)
                                    <form method="POST" action="{{ route('collection-entries.force-destroy', $collectionEntry->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $collectionEntry)
                                    <a href="{{ route('collection-entries.edit', $collectionEntry) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $collectionEntry)
                                    <form method="POST" action="{{ route('collection-entries.destroy', $collectionEntry) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No collection entries found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($collectionEntries as $collectionEntry)
                    <div class="col">
                        <x-item-card
                            icon="ti-cash"
                            icon-color="success"
                            :title="$collectionEntry->dealer?->name"
                            :title-url="$collectionEntry->dealer && ! $collectionEntry->dealer->trashed() ? route('dealers.show', $collectionEntry->dealer) : null"
                            :subtitle="$collectionEntry->payment_method->label()"
                            :status-label="$collectionEntry->trashed() ? 'Trashed' : null"
                            status-color="danger"
                        >
                            <x-slot:meta>
                                <div>Executive: {{ $collectionEntry->user?->name }}</div>
                                <div>Date: {{ $collectionEntry->collection_date->format('M d, Y') }}</div>
                                <div>Amount: {{ number_format((float) $collectionEntry->amount, 2) }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $collectionEntry->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $collectionEntry->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($collectionEntry->trashed())
                                    @can('restore', $collectionEntry)
                                        <form method="POST" action="{{ route('collection-entries.restore', $collectionEntry->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $collectionEntry)
                                        <form method="POST" action="{{ route('collection-entries.force-destroy', $collectionEntry->id) }}" data-confirm data-confirm-title="Permanently delete this record?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $collectionEntry)
                                        <a href="{{ route('collection-entries.edit', $collectionEntry) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $collectionEntry)
                                        <form method="POST" action="{{ route('collection-entries.destroy', $collectionEntry) }}" data-confirm data-confirm-title="Move this record to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No collection entries found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('collection-entries.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\CollectionEntry::class)
        <x-modal id="importModal" title="Import Collection Entries">
            <form id="importForm" method="POST" action="{{ route('collection-entries.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: employee_id, dealer_code, collection_date, amount, payment_method, reference_no, remarks.</div>
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
