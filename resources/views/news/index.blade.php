@extends('layouts.admin')

@section('title', 'News')

@section('breadcrumb')
    <li class="breadcrumb-item active">News</li>
@endsection

@section('content')
    <x-filter-bar :action="route('news.index')">
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
        @include('partials.trashed-filter', ['filters' => $filters, 'colClass' => 'col-md-3'])
    </x-filter-bar>

    <form id="bulkForm" method="POST" action="{{ route('news.bulk-destroy') }}" data-confirm data-confirm-title="Delete selected articles?">
        @csrf
        <x-data-table
            title="News"
            :create-url="auth()->user()->can('create', \App\Models\News::class) ? route('news.create') : null"
            :paginator="$newsItems"
        >
            <x-slot:thead>
                <tr>
                    <th style="width:2rem"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th></th>
                    <th>Title</th>
                    <th>Published At</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </x-slot:thead>

            @forelse ($newsItems as $article)
                <tr>
                    <td>
                        @if (! $article->trashed())
                            <input type="checkbox" name="ids[]" value="{{ $article->id }}" class="form-check-input row-checkbox">
                        @endif
                    </td>
                    <td>
                        @if ($article->cover_image)
                            <img src="{{ $article->coverImageUrl() }}" style="width:36px;height:36px;object-fit:cover" class="rounded">
                        @else
                            <i class="ti ti-news text-secondary fs-5"></i>
                        @endif
                    </td>
                    <td>
                        {{ $article->title }}
                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($article->excerpt, 60) }}</div>
                    </td>
                    <td>{{ $article->published_at?->format('M d, Y') }}</td>
                    <td>
                        @if ($article->trashed())
                            <span class="badge text-bg-danger">Trashed</span>
                        @else
                            <span class="badge text-bg-{{ $article->is_published ? 'success' : 'secondary' }}">
                                {{ $article->is_published ? 'Published' : 'Draft' }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if ($article->trashed())
                                @can('restore', $article)
                                    <form method="POST" action="{{ route('news.restore', $article->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Restore"><i class="ti ti-arrow-back-up"></i></button>
                                    </form>
                                @endcan
                                @can('forceDelete', $article)
                                    <form method="POST" action="{{ route('news.force-destroy', $article->id) }}" data-confirm data-confirm-title="Permanently delete this article?" data-confirm-text="This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete permanently"><i class="ti ti-trash-x"></i></button>
                                    </form>
                                @endcan
                            @else
                                @can('update', $article)
                                    <a href="{{ route('news.edit', $article) }}" class="btn btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                                @endcan
                                @can('delete', $article)
                                    <form method="POST" action="{{ route('news.destroy', $article) }}" data-confirm data-confirm-title="Move this article to trash?">
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
                    <td colspan="6" class="text-center text-muted py-4">No news articles found.</td>
                </tr>
            @endforelse
        </x-data-table>

        @can('news.delete')
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
