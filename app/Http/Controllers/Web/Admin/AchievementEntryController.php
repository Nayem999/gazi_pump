<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\AchievementEntriesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAchievementEntryRequest;
use App\Http\Requests\Admin\UpdateAchievementEntryRequest;
use App\Imports\AchievementEntriesImport;
use App\Models\AchievementEntry;
use App\Models\Product;
use App\Models\User;
use App\Services\AchievementEntryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class AchievementEntryController extends Controller
{
    public function __construct(private readonly AchievementEntryService $achievementEntries) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AchievementEntry::class);

        $filters = $request->only(['search', 'user_id', 'status', 'date_from', 'date_to', 'trashed']);

        return view('achievements.index', [
            'achievementEntries' => $this->achievementEntries->paginate($filters, 15, $request->user()),
            'executives' => $this->scopedExecutives($request->user()),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AchievementEntry::class);

        return view('achievements.create', [
            'executives' => $this->scopedExecutives($request->user()),
            'products' => Product::where('status', true)->visibleTo($request->user())->orderBy('name')->get(),
        ]);
    }

    public function store(StoreAchievementEntryRequest $request): RedirectResponse
    {
        $this->achievementEntries->create($request->validated());

        return redirect()->route('achievements.index')->with('success', 'Achievement recorded successfully.');
    }

    public function show(AchievementEntry $achievementEntry): View
    {
        $this->authorize('view', $achievementEntry);

        return view('achievements.show', [
            'entry' => $achievementEntry->load(['user', 'approvedBy', 'items.product']),
        ]);
    }

    public function edit(Request $request, AchievementEntry $achievementEntry): View
    {
        $this->authorize('update', $achievementEntry);

        $achievementEntry->load('items');

        return view('achievements.edit', [
            'entry' => $achievementEntry,
            'executives' => $this->scopedExecutives($request->user()),
            'products' => Product::where('status', true)->visibleTo($request->user())
                ->orWhereIn('id', $achievementEntry->items->pluck('product_id'))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateAchievementEntryRequest $request, AchievementEntry $achievementEntry): RedirectResponse
    {
        $this->achievementEntries->update($achievementEntry, $request->validated());

        return redirect()->route('achievements.index')->with('success', 'Achievement updated successfully.');
    }

    public function approve(Request $request, AchievementEntry $achievementEntry): RedirectResponse
    {
        $this->authorize('approve', $achievementEntry);

        $this->achievementEntries->approve($achievementEntry, $request->user()->id);

        return back()->with('success', 'Achievement approved.');
    }

    public function reject(Request $request, AchievementEntry $achievementEntry): RedirectResponse
    {
        $this->authorize('approve', $achievementEntry);

        $this->achievementEntries->reject($achievementEntry, $request->user()->id);

        return back()->with('success', 'Achievement rejected.');
    }

    public function destroy(AchievementEntry $achievementEntry): RedirectResponse
    {
        $this->authorize('delete', $achievementEntry);

        $this->achievementEntries->delete($achievementEntry);

        return redirect()->route('achievements.index')->with('success', 'Achievement moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $achievementEntry = AchievementEntry::withTrashed()->findOrFail($id);
        $this->authorize('restore', $achievementEntry);

        $this->achievementEntries->restore($id);

        return redirect()->route('achievements.index')->with('success', 'Achievement restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $achievementEntry = AchievementEntry::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $achievementEntry);

        $this->achievementEntries->forceDelete($id);

        return redirect()->route('achievements.index')->with('success', 'Achievement permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('achievements.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:achievement_entries,id']]);

        $count = $this->achievementEntries->bulkDelete($request->input('ids'));

        return redirect()->route('achievements.index')->with('success', "{$count} achievement(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('achievements.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:achievement_entries,id']]);

        $count = $this->achievementEntries->bulkRestore($request->input('ids'));

        return redirect()->route('achievements.index')->with('success', "{$count} achievement(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', AchievementEntry::class);

        $entries = $this->achievementEntries->paginate($request->only(['search', 'user_id', 'status', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX, $request->user())->getCollection();

        return Excel::download(new AchievementEntriesExport($entries), 'achievements-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', AchievementEntry::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new AchievementEntriesImport, $request->file('file'));

        return redirect()->route('achievements.index')->with('success', 'Achievements imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', AchievementEntry::class);

        $entries = $this->achievementEntries->paginate($request->only(['search', 'user_id', 'status', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX, $request->user())->getCollection();

        return Pdf::loadView('achievements.print', ['entries' => $entries])
            ->stream('achievements-'.now()->format('Y-m-d-His').'.pdf');
    }

    /**
     * Sales Executives selectable in the filter/assign dropdown — restricted
     * to the viewer's own territories when they have any assigned, or to
     * themself alone when Sales Executive is their sole role.
     */
    private function scopedExecutives(User $viewer): Collection
    {
        $territoryIds = $viewer->territories->pluck('id')->all();

        return User::role('Sales Executive')
            ->when($territoryIds !== [], fn ($q) => $q->whereHas('territories', fn ($t) => $t->whereIn('territories.id', $territoryIds)))
            ->when($viewer->isSalesExecutiveOnly(), fn ($q) => $q->where('id', $viewer->id))
            ->orderBy('name')
            ->get();
    }
}
