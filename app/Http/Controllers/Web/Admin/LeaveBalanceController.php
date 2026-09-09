<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\LeaveBalancesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SetUpLeaveEntitlementsRequest;
use App\Http\Requests\Admin\StoreLeaveBalanceRequest;
use App\Http\Requests\Admin\UpdateLeaveBalanceRequest;
use App\Models\LeaveBalance;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Services\LeaveTypeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * The entitlement setup screen: how many days each person is granted, per
 * leave type, per year.
 *
 * Separate from leave-requests on purpose. Approving a request spends days
 * someone already has; editing an entitlement decides how many they get,
 * which is an HR decision - so it carries its own permission set and a
 * line manager with approval rights cannot grant days.
 *
 * Days *used* are never edited here, or anywhere: they are derived from
 * approved requests (see LeaveBalance and its migration). This screen owns
 * only the half that cannot be derived.
 */
class LeaveBalanceController extends Controller
{
    private const FILTERS = ['search', 'year', 'user_id', 'leave_type_id', 'trashed'];

    public function __construct(
        private readonly LeaveBalanceService $balances,
        private readonly LeaveTypeService $leaveTypes,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveBalance::class);

        // Defaulted to this year rather than showing every year at once -
        // entitlements are read a year at a time, and the unfiltered list
        // grows by one row per person per type per year forever.
        $filters = $request->only(self::FILTERS);
        $filters['year'] ??= (string) now()->format('Y');

        return view('leave-balances.index', [
            'leaveBalances' => $this->balances->paginate($filters, 20),
            'filters' => $filters,
            'leaveTypes' => $this->leaveTypes->activeTypes(),
            'users' => $this->activeUsers(),
            'year' => (int) $filters['year'],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', LeaveBalance::class);

        return view('leave-balances.create', [
            'leaveTypes' => $this->leaveTypes->activeTypes(),
            'users' => $this->activeUsers(),
            'year' => (int) $request->input('year', now()->format('Y')),
        ]);
    }

    public function store(StoreLeaveBalanceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // saveEntitlement(), not create(): a soft-deleted entitlement for
        // the same person/type/year still holds the unique key, and
        // re-granting it is an undelete rather than a new record.
        $balance = $this->balances->saveEntitlement($data);

        return redirect()
            ->route('leave-balances.index', ['year' => $data['year']])
            ->with('success', "Entitlement saved for {$balance->user?->name}.");
    }

    public function edit(LeaveBalance $leaveBalance): View
    {
        $this->authorize('update', $leaveBalance);

        return view('leave-balances.edit', [
            'leaveBalance' => $leaveBalance->load(['user', 'leaveType']),
            'leaveTypes' => $this->leaveTypes->activeTypes(),
            'users' => $this->activeUsers(),
        ]);
    }

    public function update(UpdateLeaveBalanceRequest $request, LeaveBalance $leaveBalance): RedirectResponse
    {
        $this->balances->update($leaveBalance, $request->validated());

        return redirect()
            ->route('leave-balances.index', ['year' => $leaveBalance->year])
            ->with('success', 'Entitlement updated.');
    }

    /**
     * Seeds missing entitlement rows for a year from each type's quota.
     *
     * The convenience that makes the module usable at the start of a year:
     * without it somebody has to add one row per person per type by hand.
     * Safe to re-run - an entitlement that already exists, including one
     * that was individually adjusted or deliberately deleted, is left
     * exactly as it is.
     */
    public function setUp(SetUpLeaveEntitlementsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $year = (int) $data['year'];

        $users = isset($data['user_ids'])
            ? User::query()->whereIn('id', $data['user_ids'])->get()
            : $this->activeUsers();

        $result = $this->balances->setUpForMany($users, $year);

        return redirect()
            ->route('leave-balances.index', ['year' => $year])
            ->with('success', $this->setUpSummary($result, $year, $users->count()));
    }

    /**
     * @param  array{users: int, created: int}  $result
     */
    private function setUpSummary(array $result, int $year, int $considered): string
    {
        if ($result['created'] === 0) {
            // Said plainly rather than as a bare success: "nothing
            // happened" is the expected answer on a re-run, and reporting
            // it as a change would be misleading.
            return "Nothing to set up for {$year} — all {$considered} employee(s) already have an entitlement for every active leave type.";
        }

        return sprintf(
            'Set up %d entitlement(s) for %d employee(s) in %d. Entitlements that already existed were left unchanged.',
            $result['created'],
            $result['users'],
            $year,
        );
    }

    public function destroy(LeaveBalance $leaveBalance): RedirectResponse
    {
        $this->authorize('delete', $leaveBalance);

        $this->balances->delete($leaveBalance);

        return redirect()->route('leave-balances.index', ['year' => $leaveBalance->year])
            ->with('success', 'Entitlement moved to trash. That person now falls back to the leave type default.');
    }

    public function restore(int $id): RedirectResponse
    {
        $leaveBalance = LeaveBalance::withTrashed()->findOrFail($id);
        $this->authorize('restore', $leaveBalance);

        $this->balances->restore($id);

        return redirect()->route('leave-balances.index', ['year' => $leaveBalance->year])
            ->with('success', 'Entitlement restored.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $leaveBalance = LeaveBalance::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $leaveBalance);

        $this->balances->forceDelete($id);

        return redirect()->route('leave-balances.index')->with('success', 'Entitlement permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('leave-balances.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:leave_balances,id']]);

        $count = $this->balances->bulkDelete($request->input('ids'));

        return redirect()->route('leave-balances.index')->with('success', "{$count} entitlement(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('leave-balances.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:leave_balances,id']]);

        $count = $this->balances->bulkRestore($request->input('ids'));

        return redirect()->route('leave-balances.index')->with('success', "{$count} entitlement(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', LeaveBalance::class);

        $rows = $this->balances->paginate($request->only(self::FILTERS), PHP_INT_MAX)->getCollection();

        return Excel::download(new LeaveBalancesExport($rows), 'leave-balances-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', LeaveBalance::class);

        $rows = $this->balances->paginate($request->only(self::FILTERS), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('leave-balances.print', ['leaveBalances' => $rows])
            ->stream('leave-balances-'.now()->format('Y-m-d-His').'.pdf');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function activeUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()->where('status', true)->orderBy('name')->get();
    }
}
