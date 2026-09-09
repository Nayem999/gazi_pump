<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\LeaveTypesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLeaveTypeRequest;
use App\Http\Requests\Admin\UpdateLeaveTypeRequest;
use App\Imports\LeaveTypesImport;
use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Leave type master data - Casual, Sick, Annual and so on. Plain CRUD; the
 * workflow lives in LeaveRequestController.
 */
class LeaveTypeController extends Controller
{
    private const FILTERS = ['search', 'status', 'trashed'];

    public function __construct(private readonly LeaveTypeService $leaveTypes) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveType::class);

        return view('leave-types.index', [
            'leaveTypes' => $this->leaveTypes->paginate($request->only(self::FILTERS), 15),
            'filters' => $request->only(self::FILTERS),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', LeaveType::class);

        return view('leave-types.create');
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        $this->leaveTypes->create($request->validated());

        return redirect()->route('leave-types.index')->with('success', 'Leave type created successfully.');
    }

    public function edit(LeaveType $leaveType): View
    {
        $this->authorize('update', $leaveType);

        return view('leave-types.edit', ['leaveType' => $leaveType]);
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $this->leaveTypes->update($leaveType, $request->validated());

        return redirect()->route('leave-types.index')->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        $this->authorize('delete', $leaveType);

        $this->leaveTypes->delete($leaveType);

        return redirect()->route('leave-types.index')->with('success', 'Leave type moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $leaveType = LeaveType::withTrashed()->findOrFail($id);
        $this->authorize('restore', $leaveType);

        $this->leaveTypes->restore($id);

        return redirect()->route('leave-types.index')->with('success', 'Leave type restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $leaveType = LeaveType::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $leaveType);

        $this->leaveTypes->forceDelete($id);

        return redirect()->route('leave-types.index')->with('success', 'Leave type permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('leave-types.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:leave_types,id']]);

        $count = $this->leaveTypes->bulkDelete($request->input('ids'));

        return redirect()->route('leave-types.index')->with('success', "{$count} leave type(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('leave-types.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:leave_types,id']]);

        $count = $this->leaveTypes->bulkRestore($request->input('ids'));

        return redirect()->route('leave-types.index')->with('success', "{$count} leave type(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', LeaveType::class);

        $rows = $this->leaveTypes->paginate($request->only(self::FILTERS), PHP_INT_MAX)->getCollection();

        return Excel::download(new LeaveTypesExport($rows), 'leave-types-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', LeaveType::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new LeaveTypesImport, $request->file('file'));

        return redirect()->route('leave-types.index')->with('success', 'Leave types imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', LeaveType::class);

        $rows = $this->leaveTypes->paginate($request->only(self::FILTERS), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('leave-types.print', ['leaveTypes' => $rows])
            ->stream('leave-types-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(LeaveType $leaveType): RedirectResponse
    {
        $this->authorize('update', $leaveType);

        $this->leaveTypes->update($leaveType, ['status' => ! $leaveType->status]);

        return back()->with('success', 'Leave type status updated.');
    }
}
