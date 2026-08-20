@extends('layouts.admin')

@section('title', 'Brochures')

@section('breadcrumb')
    <li class="breadcrumb-item active">Brochures</li>
@endsection

@section('content')
    <x-filter-bar :action="route('brochures.index')">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Title..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Trashed</label>
            <select name="trashed" class="form-select">
                <option value="">Without Trashed</option>
                <option value="with" @selected(($filters['trashed'] ?? '') === 'with')>With Trashed</option>
                <option value="only" @selected(($filters['trashed'] ?? '') === 'only')>Only Trashed</option>
            </select>
        </div>
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('brochures.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected brochures?">
        @csrf
        <x-data-table
            title="Brochures"
            :create-url="auth()->user()->can('create', \App\Models\Brochure::class) ? route('brochures.create') : null"
            :paginator="$brochures"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th></th>
                    <th>Title</th>
                    <th>File</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($brochures as $brochure)
                <tr>
                    <td>
                        @if (! $brochure->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $brochure->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        @if ($brochure->cover_image)
                            <img src="{{ $brochure->coverImageUrl() }}" style="width:36px;height:36px;object-fit:cover" class="rounded">
                        @else
                            <i class="ti ti-file-type-pdf text-secondary fs-5"></i>
                        @endif
                    </td>
                    <td>{{ $brochure->title }}</td>
                    <td><a href="{{ $brochure->fileUrl() }}" download>View PDF</a></td>
                    <td>
                        @if ($brochure->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $brochure->is_published ? 'success' : 'secondary' }}">
                                {{ $brochure->is_published ? 'Published' : 'Draft' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($brochure->trashed())
                                @can('restore', $brochure)
                                    <form method="POST" action="{{ route('brochures.restore', $brochure->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $brochure)
                                    <form method="POST" action="{{ route('brochures.force-destroy', $brochure->id) }}" data-confirm data-confirm-title="Permanently delete this brochure?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $brochure)
                                    <a href="{{ route('brochures.edit', $brochure) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $brochure)
                                    <form method="POST" action="{{ route('brochures.destroy', $brochure) }}" data-confirm data-confirm-title="Move this brochure to trash?">
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
                    <td colspan="6" class="text-center text-muted py-4">No brochures found.</td>
                </tr>
            @endforelse
        </x-data-table>

        @can('brochures.delete')
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
