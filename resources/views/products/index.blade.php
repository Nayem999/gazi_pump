@extends('layouts.admin')

@section('title', 'Products')

@section('breadcrumb')
    <li class="breadcrumb-item active">Products</li>
@endsection

@section('content')
    <x-filter-bar :action="route('products.index')">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or SKU..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select">
                <option value="">All</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
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
        <div class="col-md-2">
            <label class="form-label">Trashed</label>
            <select name="trashed" class="form-select">
                <option value="">Without Trashed</option>
                <option value="with" @selected(($filters['trashed'] ?? '') === 'with')>With Trashed</option>
                <option value="only" @selected(($filters['trashed'] ?? '') === 'only')>Only Trashed</option>
            </select>
        </div>
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('products.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected products?">
        @csrf
        <x-data-table
            title="All Products"
            :create-url="auth()->user()->can('create', \App\Models\Product::class) ? route('products.create') : null"
            :export-url="auth()->user()->can('export', \App\Models\Product::class) ? route('products.export', request()->query()) : null"
            :import-url="auth()->user()->can('import', \App\Models\Product::class) ? '#importModal' : null"
            :print-url="auth()->user()->can('print', \App\Models\Product::class) ? route('products.print', request()->query()) : null"
            :paginator="$products"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th></th>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($products as $product)
                <tr>
                    <td>
                        @if (! $product->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        @if ($product->image)
                            <img src="{{ $product->imageUrl() }}" style="width:36px;height:36px;object-fit:cover" class="rounded">
                        @else
                            <i class="ti ti-package text-secondary fs-5"></i>
                        @endif
                    </td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category?->name }}</td>
                    <td>{{ number_format((float) $product->price, 2) }}</td>
                    <td>
                        @if ($product->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $product->status ? 'success' : 'secondary' }}">
                                {{ $product->status ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($product->trashed())
                                @can('restore', $product)
                                    <form method="POST" action="{{ route('products.restore', $product->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $product)
                                    <form method="POST" action="{{ route('products.force-destroy', $product->id) }}" data-confirm data-confirm-title="Permanently delete this product?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $product)
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $product)
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm data-confirm-title="Move this product to trash?">
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
                    <td colspan="8" class="text-center text-muted py-4">No products found.</td>
                </tr>
            @endforelse

            <x-slot:cards>
                @forelse ($products as $product)
                    <div class="col">
                        <x-item-card
                            icon="ti-package"
                            icon-color="primary"
                            :image="$product->image ? $product->imageUrl() : null"
                            :title="$product->name"
                            :subtitle="$product->sku"
                            :status-label="$product->trashed() ? 'Trashed' : ($product->status ? 'Active' : 'Inactive')"
                            :status-color="$product->trashed() ? 'danger' : ($product->status ? 'success' : 'secondary')"
                        >
                            <x-slot:meta>
                                <div>Category: {{ $product->category?->name }}</div>
                                <div>Price: {{ number_format((float) $product->price, 2) }}</div>
                            </x-slot:meta>
                            <x-slot:checkbox>
                                @if (! $product->trashed())
                                    <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="form-check-input row-checkbox">
                                @endif
                            </x-slot:checkbox>
                            <x-slot:actions>
                                @if ($product->trashed())
                                    @can('restore', $product)
                                        <form method="POST" action="{{ route('products.restore', $product->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $product)
                                        <form method="POST" action="{{ route('products.force-destroy', $product->id) }}" data-confirm data-confirm-title="Permanently delete this product?" data-confirm-text="This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                        </form>
                                    @endcan
                                @else
                                    @can('update', $product)
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('delete', $product)
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm data-confirm-title="Move this product to trash?">
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
                    <div class="col-12 text-center text-muted py-4">No products found.</div>
                @endforelse
            </x-slot:cards>
        </x-data-table>

        @can('products.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>

    @can('import', \App\Models\Product::class)
        <x-modal id="importModal" title="Import Products">
            <form id="importForm" method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel/CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Columns: category_code, name, sku, price.</div>
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
