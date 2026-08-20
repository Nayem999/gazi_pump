<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Enums\VisitRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreVisitRequestRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VisitRequestController extends Controller
{
    public function index(Request $request): View
    {
        return view('portal.visit-requests.index', [
            'visitRequests' => $request->user('customer')->visitRequests()->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('portal.visit-requests.create');
    }

    public function store(StoreVisitRequestRequest $request): RedirectResponse
    {
        $request->user('customer')->visitRequests()->create([
            ...$request->validated(),
            'status' => VisitRequestStatus::Pending,
        ]);

        return redirect()->route('portal.visit-requests.index')->with('success', 'Your visit request has been submitted.');
    }
}
