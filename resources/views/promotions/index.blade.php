@extends('layouts.admin')

@section('title', 'Promotions')

@section('breadcrumb')
    <li class="breadcrumb-item active">Promotions</li>
@endsection

@section('content')
    <x-filter-bar :action="route('promotions.index')">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Title..." value="{{ $filters['search'] ?? '' }}">
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

    <form id="bulkForm" method="POST" action="{{ route('promotions.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected promotions?">
        @csrf
        <x-data-table
            title="Promotions"
            :create-url="auth()->user()->can('create', \App\Models\Promotion::class) ? route('promotions.create') : null"
            :paginator="$promotions"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th></th>
                    <th>Title</th>
                    <th>Starts</th>
                    <th>Ends</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($promotions as $promotion)
                <tr>
                    <td>
                        @if (! $promotion->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $promotion->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        @if ($promotion->image)
                            <img src="{{ $promotion->imageUrl() }}" style="width:36px;height:36px;object-fit:cover" class="rounded">
                        @else
                            <i class="ti ti-discount-2 text-secondary fs-5"></i>
                        @endif
                    </td>
                    <td>
                        {{ $promotion->title }}
                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($promotion->description, 60) }}</div>
                    </td>
                    <td>{{ $promotion->starts_at?->format('d M Y') }}</td>
                    <td>{{ $promotion->ends_at?->format('d M Y') }}</td>
                    <td>
                        @if ($promotion->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $promotion->is_active ? 'success' : 'secondary' }}">
                                {{ $promotion->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($promotion->trashed())
                                @can('restore', $promotion)
                                    <form method="POST" action="{{ route('promotions.restore', $promotion->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $promotion)
                                    <form method="POST" action="{{ route('promotions.force-destroy', $promotion->id) }}" data-confirm data-confirm-title="Permanently delete this promotion?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $promotion)
                                    <a href="{{ route('promotions.edit', $promotion) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $promotion)
                                    <form method="POST" action="{{ route('promotions.destroy', $promotion) }}" data-confirm data-confirm-title="Move this promotion to trash?">
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
                    <td colspan="7" class="text-center text-muted py-4">No promotions found.</td>
                </tr>
            @endforelse
        </x-data-table>

        @can('promotions.delete')
            <div class="mt-2">
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Delete Selected</button>
            </div>
        @endcan
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('selectAll')?.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach((cb) => { cb.checked = this.checked; });
        });
    </script>
@endpush
