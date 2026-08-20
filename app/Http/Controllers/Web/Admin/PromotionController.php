<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromotionRequest;
use App\Http\Requests\Admin\UpdatePromotionRequest;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $promotions) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Promotion::class);

        return view('promotions.index', [
            'promotions' => $this->promotions->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Promotion::class);

        return view('promotions.create');
    }

    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $this->promotions->create($request->validated(), $request->file('image'));

        return redirect()->route('promotions.index')->with('success', 'Promotion created successfully.');
    }

    public function edit(Promotion $promotion): View
    {
        $this->authorize('update', $promotion);

        return view('promotions.edit', ['promotion' => $promotion]);
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $this->promotions->update($promotion, $request->validated(), $request->file('image'));

        return redirect()->route('promotions.index')->with('success', 'Promotion updated successfully.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $this->authorize('delete', $promotion);

        $this->promotions->delete($promotion);

        return redirect()->route('promotions.index')->with('success', 'Promotion moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $promotion = Promotion::withTrashed()->findOrFail($id);
        $this->authorize('restore', $promotion);

        $this->promotions->restore($id);

        return redirect()->route('promotions.index')->with('success', 'Promotion restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $promotion = Promotion::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $promotion);

        $this->promotions->forceDelete($id);

        return redirect()->route('promotions.index')->with('success', 'Promotion permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('promotions.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:promotions,id']]);

        $count = $this->promotions->bulkDelete($request->input('ids'));

        return redirect()->route('promotions.index')->with('success', "{$count} promotion(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('promotions.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:promotions,id']]);

        $count = $this->promotions->bulkRestore($request->input('ids'));

        return redirect()->route('promotions.index')->with('success', "{$count} promotion(s) restored.");
    }

    public function toggleStatus(Promotion $promotion): RedirectResponse
    {
        $this->authorize('update', $promotion);

        $this->promotions->update($promotion, ['is_active' => ! $promotion->is_active]);

        return back()->with('success', 'Promotion status updated.');
    }
}
