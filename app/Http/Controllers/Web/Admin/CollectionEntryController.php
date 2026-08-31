<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ChequeStatus;
use App\Enums\PaymentMethod;
use App\Exports\CollectionEntriesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCollectionEntryRequest;
use App\Http\Requests\Admin\UpdateCollectionEntryRequest;
use App\Imports\CollectionEntriesImport;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Setting;
use App\Models\Territory;
use App\Models\User;
use App\Services\CollectionEntryService;
use App\Services\CollectionOtpService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CollectionEntryController extends Controller
{
    public function __construct(
        private readonly CollectionEntryService $collectionEntries,
        private readonly CollectionOtpService $otps,
    ) {}

    public function sendOtp(Request $request): JsonResponse
    {
        $this->authorize('create', CollectionEntry::class);

        $data = $request->validate([
            'dealer_id' => [
                'required', 'integer', Rule::exists('dealers', 'id'),
                function ($attribute, $value, $fail) use ($request) {
                    if (! Dealer::query()->visibleTo($request->user())->whereKey($value)->exists()) {
                        $fail('This dealer is outside your assigned territories.');
                    }
                },
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ]);

        $result = $this->otps->send(
            $request->user(),
            (int) $data['dealer_id'],
            (float) $data['amount'],
            PaymentMethod::from($data['payment_method']),
        );

        return response()->json($result);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CollectionEntry::class);

        $filters = $request->only(['search', 'user_id', 'dealer_id', 'territory_id', 'payment_method', 'status', 'date_from', 'date_to', 'trashed']);

        return view('collection-entries.index', [
            'collectionEntries' => $this->collectionEntries->paginate($filters, 15, $request->user()),
            'total' => $this->collectionEntries->total($filters, $request->user()),
            'executives' => $this->scopedExecutives($request->user()),
            'territories' => Territory::query()->visibleTo($request->user())->where('status', true)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::cases(),
            'filters' => $filters,
        ]);
    }

    public function show(CollectionEntry $collectionEntry): View
    {
        $this->authorize('view', $collectionEntry);

        return view('collection-entries.show', ['collectionEntry' => $collectionEntry->load(['user', 'dealer', 'approvedBy'])]);
    }

    public function downloadPdf(CollectionEntry $collectionEntry): mixed
    {
        $this->authorize('view', $collectionEntry);

        $collectionEntry->load(['user', 'dealer']);

        return Pdf::loadView('collection-entries.detail-pdf', ['collectionEntry' => $collectionEntry, 'setting' => Setting::current()])
            ->stream('collection-'.$collectionEntry->id.'-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CollectionEntry::class);

        return view('collection-entries.create', [
            'executives' => $this->scopedExecutives($request->user()),
            'dealers' => Dealer::query()->visibleTo($request->user())->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::cases(),
            'outstandingBalances' => $this->outstandingBalances($request->user()),
        ]);
    }

    public function store(StoreCollectionEntryRequest $request): RedirectResponse
    {
        $this->collectionEntries->create($request->safe()->except('cheque_image'), $request->file('cheque_image'));

        return redirect()->route('collection-entries.index')->with('success', 'Collection recorded successfully.');
    }

    public function edit(Request $request, CollectionEntry $collectionEntry): View
    {
        $this->authorize('update', $collectionEntry);

        return view('collection-entries.edit', [
            'collectionEntry' => $collectionEntry,
            'executives' => $this->scopedExecutives($request->user()),
            // Territory-scoped like the create form, but also keeps this
            // entry's own dealer even if it's since fallen outside the
            // viewer's territories, so re-editing an old entry never
            // silently drops its dealer selection.
            'dealers' => Dealer::query()->visibleTo($request->user())->orWhere('id', $collectionEntry->dealer_id)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::cases(),
            'outstandingBalances' => $this->outstandingBalances($request->user()),
        ]);
    }

    public function update(UpdateCollectionEntryRequest $request, CollectionEntry $collectionEntry): RedirectResponse
    {
        $this->collectionEntries->update($collectionEntry, $request->safe()->except('cheque_image'), $request->file('cheque_image'));

        return redirect()->route('collection-entries.index')->with('success', 'Collection updated successfully.');
    }

    public function updateChequeStatus(Request $request, CollectionEntry $collectionEntry): RedirectResponse
    {
        $this->authorize('update', $collectionEntry);

        $request->validate(['cheque_status' => ['required', Rule::enum(ChequeStatus::class)]]);

        $this->collectionEntries->updateChequeStatus($collectionEntry, ChequeStatus::from($request->string('cheque_status')->value()));

        return back()->with('success', 'Cheque status updated.');
    }

    public function approve(Request $request, CollectionEntry $collectionEntry): RedirectResponse
    {
        $this->authorize('approve', $collectionEntry);

        $this->collectionEntries->approve($collectionEntry, $request->user()->id);

        return back()->with('success', 'Collection approved.');
    }

    public function reject(Request $request, CollectionEntry $collectionEntry): RedirectResponse
    {
        $this->authorize('approve', $collectionEntry);

        $this->collectionEntries->reject($collectionEntry, $request->user()->id);

        return back()->with('success', 'Collection rejected.');
    }

    public function destroy(CollectionEntry $collectionEntry): RedirectResponse
    {
        $this->authorize('delete', $collectionEntry);

        $this->collectionEntries->delete($collectionEntry);

        return redirect()->route('collection-entries.index')->with('success', 'Collection moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $collectionEntry = CollectionEntry::withTrashed()->findOrFail($id);
        $this->authorize('restore', $collectionEntry);

        $this->collectionEntries->restore($id);

        return redirect()->route('collection-entries.index')->with('success', 'Collection restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $collectionEntry = CollectionEntry::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $collectionEntry);

        $this->collectionEntries->forceDelete($id);

        return redirect()->route('collection-entries.index')->with('success', 'Collection permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('collection-entries.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:collection_entries,id']]);

        $count = $this->collectionEntries->bulkDelete($request->input('ids'));

        return redirect()->route('collection-entries.index')->with('success', "{$count} collection(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('collection-entries.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:collection_entries,id']]);

        $count = $this->collectionEntries->bulkRestore($request->input('ids'));

        return redirect()->route('collection-entries.index')->with('success', "{$count} collection(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', CollectionEntry::class);

        $collectionEntries = $this->collectionEntries->paginate($request->only(['search', 'user_id', 'dealer_id', 'territory_id', 'payment_method', 'status', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX, $request->user())->getCollection();

        return Excel::download(new CollectionEntriesExport($collectionEntries), 'collection-entries-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', CollectionEntry::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(app(CollectionEntriesImport::class), $request->file('file'));

        return redirect()->route('collection-entries.index')->with('success', 'Collections imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', CollectionEntry::class);

        $collectionEntries = $this->collectionEntries->paginate($request->only(['search', 'user_id', 'dealer_id', 'territory_id', 'payment_method', 'status', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX, $request->user())->getCollection();

        return Pdf::loadView('collection-entries.print', ['collectionEntries' => $collectionEntries])
            ->stream('collection-entries-'.now()->format('Y-m-d-His').'.pdf');
    }

    /**
     * @return array<int, float>
     */
    private function outstandingBalances(User $viewer): array
    {
        return Dealer::query()->visibleTo($viewer)->get()->mapWithKeys(
            fn (Dealer $dealer) => [$dealer->id => $this->collectionEntries->outstandingBalance($dealer->id)]
        )->all();
    }

    /**
     * Sales Executives selectable in the filter dropdown — restricted to the
     * viewer's own territories when they have any assigned, or to themself
     * alone when Sales Executive is their sole role.
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
