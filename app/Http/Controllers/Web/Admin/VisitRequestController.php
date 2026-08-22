<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\VisitRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVisitRequestStatusRequest;
use App\Models\VisitRequest;
use App\Services\VisitRequestService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VisitRequestController extends Controller
{
    public function __construct(private readonly VisitRequestService $visitRequests) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', VisitRequest::class);

        return view('visit-requests.index', [
            'visitRequests' => $this->visitRequests->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
            'statuses' => VisitRequestStatus::cases(),
        ]);
    }

    public function show(VisitRequest $visitRequest): View
    {
        $this->authorize('view', $visitRequest);

        return view('visit-requests.show', [
            'visitRequest' => $visitRequest->load('customerAccount'),
            'statuses' => VisitRequestStatus::cases(),
        ]);
    }

    public function updateStatus(UpdateVisitRequestStatusRequest $request, VisitRequest $visitRequest): RedirectResponse
    {
        $this->visitRequests->updateStatus($visitRequest, VisitRequestStatus::from($request->validated('status')));

        return redirect()->route('visit-requests.show', $visitRequest)->with('success', 'Visit request status updated successfully.');
    }
}
