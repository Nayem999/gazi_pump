<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\CashHandoverStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCashHandoverRequest;
use App\Models\CashHandover;
use App\Models\User;
use App\Services\CashHandoverService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CashHandoverController extends Controller
{
    public function __construct(private readonly CashHandoverService $cashHandovers) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CashHandover::class);

        $filters = $request->only(['user_id', 'status', 'date_from', 'date_to', 'trashed']);

        return view('cash-handovers.index', [
            'cashHandovers' => $this->cashHandovers->paginate($filters, 15),
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'statuses' => CashHandoverStatus::cases(),
            'filters' => $filters,
            'dailyLimit' => $this->cashHandovers->dailyLimit(),
            'cashInHand' => isset($filters['user_id']) && $filters['user_id'] !== ''
                ? $this->cashHandovers->cashInHand((int) $filters['user_id'])
                : null,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CashHandover::class);

        $executives = User::role('Sales Executive')->orderBy('name')->get();

        return view('cash-handovers.create', [
            'executives' => $executives,
            'cashInHand' => $executives->mapWithKeys(
                fn (User $executive) => [$executive->id => $this->cashHandovers->cashInHand($executive->id)]
            ),
            'dailyLimit' => $this->cashHandovers->dailyLimit(),
        ]);
    }

    public function store(StoreCashHandoverRequest $request): RedirectResponse
    {
        $this->cashHandovers->create($request->validated());

        return redirect()->route('cash-handovers.index')->with('success', 'Cash handover recorded.');
    }

    public function show(CashHandover $cashHandover): View
    {
        $this->authorize('view', $cashHandover);

        return view('cash-handovers.show', [
            'cashHandover' => $cashHandover->load(['user', 'confirmedBy']),
            'cashInHand' => $this->cashHandovers->cashInHand($cashHandover->user_id),
            'dailyLimit' => $this->cashHandovers->dailyLimit(),
        ]);
    }

    public function confirm(Request $request, CashHandover $cashHandover): RedirectResponse
    {
        $this->authorize('confirm', $cashHandover);

        $this->cashHandovers->confirm($cashHandover, $request->user()->id);

        return back()->with('success', 'Cash handover confirmed.');
    }

    public function reject(Request $request, CashHandover $cashHandover): RedirectResponse
    {
        $this->authorize('confirm', $cashHandover);

        $this->cashHandovers->reject($cashHandover, $request->user()->id);

        return back()->with('success', 'Cash handover rejected.');
    }

    public function destroy(CashHandover $cashHandover): RedirectResponse
    {
        $this->authorize('delete', $cashHandover);

        $this->cashHandovers->delete($cashHandover);

        return redirect()->route('cash-handovers.index')->with('success', 'Cash handover moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $cashHandover = CashHandover::withTrashed()->findOrFail($id);
        $this->authorize('restore', $cashHandover);

        $this->cashHandovers->restore($id);

        return redirect()->route('cash-handovers.index')->with('success', 'Cash handover restored.');
    }
}
