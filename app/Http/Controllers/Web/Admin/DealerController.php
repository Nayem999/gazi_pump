<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\DealersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDealerRequest;
use App\Http\Requests\Admin\UpdateDealerRequest;
use App\Imports\DealersImport;
use App\Models\Dealer;
use App\Models\Division;
use App\Models\Setting;
use App\Services\DealerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DealerController extends Controller
{
    public function __construct(private readonly DealerService $dealers) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Dealer::class);

        $filterKeys = ['search', 'division_id', 'district_id', 'thana_id', 'territory_id', 'status', 'trashed'];

        return view('dealers.index', [
            'dealers' => $this->dealers->paginate($request->only($filterKeys), 15),
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
            'filters' => $request->only($filterKeys),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Dealer::class);

        return view('dealers.create', [
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreDealerRequest $request): RedirectResponse
    {
        $this->dealers->create($request->safe()->except('image'), $request->file('image'));

        return redirect()->route('dealers.index')->with('success', 'Dealer created successfully.');
    }

    public function show(Dealer $dealer): View
    {
        $this->authorize('view', $dealer);

        return view('dealers.show', ['dealer' => $dealer->load(['territory', 'thana', 'district', 'division'])]);
    }

    public function downloadPdf(Dealer $dealer): mixed
    {
        $this->authorize('view', $dealer);

        $dealer->load(['territory', 'thana', 'district', 'division']);

        return Pdf::loadView('dealers.detail-pdf', ['dealer' => $dealer, 'setting' => Setting::current()])
            ->stream('dealer-'.$dealer->id.'-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function edit(Dealer $dealer): View
    {
        $this->authorize('update', $dealer);

        return view('dealers.edit', [
            'dealer' => $dealer->load(['territory', 'thana', 'district', 'division']),
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateDealerRequest $request, Dealer $dealer): RedirectResponse
    {
        $this->dealers->update($dealer, $request->safe()->except('image'), $request->file('image'));

        return redirect()->route('dealers.index')->with('success', 'Dealer updated successfully.');
    }

    public function destroy(Dealer $dealer): RedirectResponse
    {
        $this->authorize('delete', $dealer);

        $this->dealers->delete($dealer);

        return redirect()->route('dealers.index')->with('success', 'Dealer moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $dealer = Dealer::withTrashed()->findOrFail($id);
        $this->authorize('restore', $dealer);

        $this->dealers->restore($id);

        return redirect()->route('dealers.index')->with('success', 'Dealer restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $dealer = Dealer::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $dealer);

        $this->dealers->forceDelete($id);

        return redirect()->route('dealers.index')->with('success', 'Dealer permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('dealers.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:dealers,id']]);

        $count = $this->dealers->bulkDelete($request->input('ids'));

        return redirect()->route('dealers.index')->with('success', "{$count} dealer(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('dealers.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:dealers,id']]);

        $count = $this->dealers->bulkRestore($request->input('ids'));

        return redirect()->route('dealers.index')->with('success', "{$count} dealer(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Dealer::class);

        $dealers = $this->dealers->paginate($request->only(['search', 'territory_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new DealersExport($dealers), 'dealers-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Dealer::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new DealersImport, $request->file('file'));

        return redirect()->route('dealers.index')->with('success', 'Dealers imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Dealer::class);

        $dealers = $this->dealers->paginate($request->only(['search', 'territory_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('dealers.print', ['dealers' => $dealers])
            ->stream('dealers-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Dealer $dealer): RedirectResponse
    {
        $this->authorize('update', $dealer);

        $this->dealers->update($dealer, ['status' => ! $dealer->status]);

        return back()->with('success', 'Dealer status updated.');
    }

    public function options(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Dealer::class);

        /**
         * Visit Plan create: once a Territory is picked, every dealer in it
         * gets auto-added — this needs to be the complete list, not a
         * capped search suggestion set.
         */
        if ($request->integer('territory_id')) {
            return response()->json(
                Dealer::query()
                    ->where('status', true)
                    ->where('territory_id', $request->integer('territory_id'))
                    ->orderBy('name')
                    ->get(['id', 'name', 'dealer_code'])
            );
        }

        $search = trim((string) $request->string('search'));

        return response()->json(
            Dealer::query()
                ->where('status', true)
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('name', 'like', "%{$search}%")
                            ->orWhere('dealer_code', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->limit(50)
                ->get(['id', 'name', 'dealer_code'])
        );
    }
}
