<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsRequest;
use App\Http\Requests\Admin\UpdateNewsRequest;
use App\Models\News;
use App\Services\NewsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(private readonly NewsService $news) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', News::class);

        return view('news.index', [
            'newsItems' => $this->news->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', News::class);

        return view('news.create');
    }

    public function store(StoreNewsRequest $request): RedirectResponse
    {
        $this->news->create($request->validated(), $request->file('cover_image'));

        return redirect()->route('news.index')->with('success', 'News article created successfully.');
    }

    public function edit(News $news): View
    {
        $this->authorize('update', $news);

        return view('news.edit', ['news' => $news]);
    }

    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $this->news->update($news, $request->validated(), $request->file('cover_image'));

        return redirect()->route('news.index')->with('success', 'News article updated successfully.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $this->authorize('delete', $news);

        $this->news->delete($news);

        return redirect()->route('news.index')->with('success', 'News article moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $news = News::withTrashed()->findOrFail($id);
        $this->authorize('restore', $news);

        $this->news->restore($id);

        return redirect()->route('news.index')->with('success', 'News article restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $news = News::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $news);

        $this->news->forceDelete($id);

        return redirect()->route('news.index')->with('success', 'News article permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('news.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:news,id']]);

        $count = $this->news->bulkDelete($request->input('ids'));

        return redirect()->route('news.index')->with('success', "{$count} article(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('news.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:news,id']]);

        $count = $this->news->bulkRestore($request->input('ids'));

        return redirect()->route('news.index')->with('success', "{$count} article(s) restored.");
    }

    public function toggleStatus(News $news): RedirectResponse
    {
        $this->authorize('update', $news);

        $this->news->update($news, ['is_published' => ! $news->is_published]);

        return back()->with('success', 'News article status updated.');
    }
}
