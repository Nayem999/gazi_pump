@extends('layouts.admin')

@section('title', 'FAQs')

@section('breadcrumb')
    <li class="breadcrumb-item active">FAQs</li>
@endsection

@section('content')
    <x-filter-bar :action="route('faqs.index')">
        <div class="col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Question..." value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
            </select>
        </div>
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('faqs.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected FAQs?">
        @csrf
        <x-data-table
            title="FAQs"
            :create-url="auth()->user()->can('create', \App\Models\Faq::class) ? route('faqs.create') : null"
            :paginator="$faqs"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Order</th>
                    <th>Question</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($faqs as $faq)
                <tr>
                    <td>
                        @if (! $faq->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $faq->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>{{ $faq->sort_order }}</td>
                    <td>
                        {{ $faq->question }}
                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($faq->answer, 60) }}</div>
                    </td>
                    <td>
                        @if ($faq->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $faq->is_published ? 'success' : 'secondary' }}">
                                {{ $faq->is_published ? 'Published' : 'Draft' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($faq->trashed())
                                @can('restore', $faq)
                                    <form method="POST" action="{{ route('faqs.restore', $faq->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $faq)
                                    <form method="POST" action="{{ route('faqs.force-destroy', $faq->id) }}" data-confirm data-confirm-title="Permanently delete this FAQ?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $faq)
                                    <a href="{{ route('faqs.edit', $faq) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $faq)
                                    <form method="POST" action="{{ route('faqs.destroy', $faq) }}" data-confirm data-confirm-title="Move this FAQ to trash?">
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
                    <td colspan="5" class="text-center text-muted py-4">No FAQs found.</td>
                </tr>
            @endforelse
        </x-data-table>

        @can('faqs.delete')
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
