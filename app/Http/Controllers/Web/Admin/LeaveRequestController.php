<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\LeaveRequestsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLeaveRequestRequest;
use App\Http\Requests\Admin\UpdateLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Services\LeaveRequestService;
use App\Services\LeaveTypeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Leave requests: submission, the manager's decision, and withdrawal.
 *
 * Not registered through the generic CRUD route factory - this is a
 * workflow, and approve/reject/cancel are the actions that matter. There
 * is deliberately no import: leave that nobody decided is not leave.
 */
class LeaveRequestController extends Controller
{
    private const FILTERS = ['search', 'status', 'leave_type_id', 'user_id', 'date_from', 'date_to', 'trashed'];

    public function __construct(
        private readonly LeaveRequestService $leaveRequests,
        private readonly LeaveTypeService $leaveTypes,
        private readonly LeaveBalanceService $balances,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        return view('leave-requests.index', [
            // The viewer is passed through so the repository scopes the
            // list: a Sales Executive sees only their own requests.
            'leaveRequests' => $this->leaveRequests->paginate($request->only(self::FILTERS), 15, $request->user()),
            'filters' => $request->only(self::FILTERS),
            'leaveTypes' => $this->leaveTypes->activeTypes(),
            'statuses' => \App\Enums\LeaveStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', LeaveRequest::class);

        return view('leave-requests.create', [
            'leaveTypes' => $this->leaveTypes->activeTypes(),
            'users' => $this->assignableUsers($request->user()),
            'balances' => $this->balances->forUser($request->user()),
        ]);
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // A manager may file on someone's behalf; everyone else files for
        // themselves, and the request class is what enforces that.
        $subject = isset($data['user_id'])
            ? User::findOrFail($data['user_id'])
            : $request->user();

        $this->leaveRequests->submit($subject, $data);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted.');
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        $this->authorize('view', $leaveRequest);

        return view('leave-requests.show', [
            'leaveRequest' => $leaveRequest->load(['user', 'leaveType', 'approver']),
            // What deciding this request would leave them with.
            'balance' => $this->leaveRequests->balanceContextFor($leaveRequest),
        ]);
    }

    public function edit(LeaveRequest $leaveRequest): View
    {
        $this->authorize('update', $leaveRequest);

        return view('leave-requests.edit', [
            'leaveRequest' => $leaveRequest,
            'leaveTypes' => $this->leaveTypes->activeTypes(),
        ]);
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        // Routed back through submit()'s rules rather than a plain update:
        // changing the dates has to re-run the overlap check and
        // recalculate the working-day count, which a field-by-field save
        // would silently skip.
        $this->leaveRequests->delete($leaveRequest);
        $this->leaveRequests->submit($leaveRequest->user, $request->validated());

        return redirect()->route('leave-requests.index')->with('success', 'Leave request updated.');
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $request->validate(['decision_remarks' => ['nullable', 'string', 'max:500']]);

        $this->leaveRequests->approve($leaveRequest, $request->user(), $request->input('decision_remarks'));

        return back()->with('success', 'Leave approved. The dates are now marked as leave in attendance.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $request->validate(['decision_remarks' => ['required', 'string', 'max:500']]);

        $this->leaveRequests->reject($leaveRequest, $request->user(), $request->input('decision_remarks'));

        return back()->with('success', 'Leave request rejected.');
    }

    public function cancel(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('cancel', $leaveRequest);

        $this->leaveRequests->cancel($leaveRequest);

        return back()->with('success', 'Leave request cancelled.');
    }

    public function destroy(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('delete', $leaveRequest);

        $this->leaveRequests->delete($leaveRequest);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $leaveRequest = LeaveRequest::withTrashed()->findOrFail($id);
        $this->authorize('restore', $leaveRequest);

        $this->leaveRequests->restore($id);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request restored.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $leaveRequest = LeaveRequest::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $leaveRequest);

        $this->leaveRequests->forceDelete($id);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('leave-requests.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:leave_requests,id']]);

        $count = $this->leaveRequests->bulkDelete($request->input('ids'));

        return redirect()->route('leave-requests.index')->with('success', "{$count} leave request(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('leave-requests.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:leave_requests,id']]);

        $count = $this->leaveRequests->bulkRestore($request->input('ids'));

        return redirect()->route('leave-requests.index')->with('success', "{$count} leave request(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', LeaveRequest::class);

        $rows = $this->leaveRequests
            ->paginate($request->only(self::FILTERS), PHP_INT_MAX, $request->user())
            ->getCollection();

        return Excel::download(new LeaveRequestsExport($rows), 'leave-requests-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', LeaveRequest::class);

        $rows = $this->leaveRequests
            ->paginate($request->only(self::FILTERS), PHP_INT_MAX, $request->user())
            ->getCollection();

        return Pdf::loadView('leave-requests.print', ['leaveRequests' => $rows])
            ->stream('leave-requests-'.now()->format('Y-m-d-His').'.pdf');
    }

    /** The balance screen: who has what left, for one person and year. */
    public function balances(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $viewer = $request->user();
        $subject = $request->filled('user_id')
            ? User::findOrFail($request->integer('user_id'))
            : $viewer;

        // An executive can only ever look at their own balance, whatever
        // the query string says.
        if ($viewer->isSalesExecutiveOnly() && $subject->id !== $viewer->id) {
            abort(403);
        }

        $year = (int) $request->input('year', now()->format('Y'));

        return view('leave-requests.balances', [
            'subject' => $subject,
            'year' => $year,
            'balances' => $this->balances->forUser($subject, $year),
            'users' => $this->assignableUsers($viewer),
        ]);
    }

    /**
     * People this viewer may file or view leave for. An executive only
     * ever gets themselves.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function assignableUsers(User $viewer): \Illuminate\Support\Collection
    {
        if ($viewer->isSalesExecutiveOnly()) {
            return collect([$viewer]);
        }

        return User::query()->where('status', true)->orderBy('name')->get();
    }
}
