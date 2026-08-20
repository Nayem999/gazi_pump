<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqRequest;
use App\Http\Requests\Admin\UpdateFaqRequest;
use App\Models\Faq;
use App\Services\FaqService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(private readonly FaqService $faqs) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Faq::class);

        return view('faqs.index', [
            'faqs' => $this->faqs->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Faq::class);

        return view('faqs.create');
    }

    public function store(StoreFaqRequest $request): RedirectResponse
    {
        $this->faqs->create($request->validated());

        return redirect()->route('faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function edit(Faq $faq): View
    {
        $this->authorize('update', $faq);

        return view('faqs.edit', ['faq' => $faq]);
    }

    public function update(UpdateFaqRequest $request, Faq $faq): RedirectResponse
    {
        $this->faqs->update($faq, $request->validated());

        return redirect()->route('faqs.index')->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $this->authorize('delete', $faq);

        $this->faqs->delete($faq);

        return redirect()->route('faqs.index')->with('success', 'FAQ moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $faq = Faq::withTrashed()->findOrFail($id);
        $this->authorize('restore', $faq);

        $this->faqs->restore($id);

        return redirect()->route('faqs.index')->with('success', 'FAQ restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $faq = Faq::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $faq);

        $this->faqs->forceDelete($id);

        return redirect()->route('faqs.index')->with('success', 'FAQ permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('faqs.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:faqs,id']]);

        $count = $this->faqs->bulkDelete($request->input('ids'));

        return redirect()->route('faqs.index')->with('success', "{$count} FAQ(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('faqs.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:faqs,id']]);

        $count = $this->faqs->bulkRestore($request->input('ids'));

        return redirect()->route('faqs.index')->with('success', "{$count} FAQ(s) restored.");
    }

    public function toggleStatus(Faq $faq): RedirectResponse
    {
        $this->authorize('update', $faq);

        $this->faqs->update($faq, ['is_published' => ! $faq->is_published]);

        return back()->with('success', 'FAQ status updated.');
    }
}
