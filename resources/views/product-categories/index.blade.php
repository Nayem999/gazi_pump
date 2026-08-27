@extends('layouts.admin')

@section('title', 'Product Categories')

@section('breadcrumb')
    <li class="breadcrumb-item active">Product Categories</li>
@endsection

@section('content')
    <x-filter-bar :action="route('product-categories.index')">
        <div class="col-md-6">
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
        <div class="col-md-3">
            <label class="form-label">Parent</label>
            <select name="parent_id" class="form-select">
                <option value="">All</option>
                <option value="none" @selected(($filters['parent_id'] ?? '') === 'none')>Top-level only</option>
                @foreach ($topLevelCategories as $parentCategory)
                    <option value="{{ $parentCategory->id }}" @selected((string) ($filters['parent_id'] ?? '') === (string) $parentCategory->id)>Under: {{ $parentCategory->name }}</option>
                @endforeach
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('product-categories.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected categories?">
        @csrf
        <x-data-table
            title="All Product Categories"
            :create-url="auth()->user()->can('create', \App\Models\ProductCategory::class) ? route('product-categories.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\ProductCategory::class) ? route('product-categories.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\ProductCategory::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\ProductCategory::class) ? route('product-categories.print', request()->query()) : null"
            :paginator="$categories"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Parent</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($categories as $category)
                <tr>
                    <td>
                        @if (! $category->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $category->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $category->code }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->parent?->name ?? '—' }}</td>
                    <td><span class="badge text-bg-secondary">{{ $category->products_count }}</span></td>
                    <td>
                        @if ($category->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $category->status ? 'success' : 'secondary' }}">
                                {{ $category->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($category->trashed())
                                @can('restore', $category)
                                    <form method="POST" action="{{ route('product-categories.restore', $category->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $category)
                                    <form method="POST" action="{{ route('product-categories.force-destroy', $category->id) }}" data-confirm data-confirm-title="Permanently delete this category?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $category)
                                    <a href="{{ route('product-categories.edit', $category) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $category)
                                    <form method="POST" action="{{ route('product-categories.destroy', $category) }}" data-confirm data-confirm-title="Move this category to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No product categories found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($categories as $category)
                    <div class="col">
                        <x-item-card
                            icon="ti-tags"
                            icon-color="primary"
                            :title="$category->name"
                            :subtitle="$category->code"
                            :status-label="$category->trashed() ? 'Trashed' : ($category->status ? 'Active' : 'Inactive')"
                            :status-color="$category->trashed() ? 'danger' : ($category->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Parent: {{ $category->parent?->name ?? 'Top-level' }}</div>
                                <div>Products: {{ $category->products_count }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $category->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $category->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($category->trashed())
                                    @can('restore', $category)
                                        <form method="POST" action="{{ route('product-categories.restore', $category->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $category)
                                        <form method="POST" action="{{ route('product-categories.force-destroy', $category->id) }}" data-confirm data-confirm-title="Permanently delete this category?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $category)
                                        <a href="{{ route('product-categories.edit', $category) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $category)
                                        <form method="POST" action="{{ route('product-categories.destroy', $category) }}" data-confirm data-confirm-title="Move this category to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No product categories found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('product-categories.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\ProductCategory::class)
        <x-modal id="importModal" title="Import Product Categories">
            <form id="importForm" method="POST" action="{{ route('product-categories.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: name, code, parent_code (optional, must match an existing top-level category's code), description.</div>
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
