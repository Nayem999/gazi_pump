<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateInquiryStatusRequest;
use App\Models\Inquiry;
use App\Services\InquiryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function __construct(private readonly InquiryService $inquiries) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Inquiry::class);

        return view('inquiries.index', [
            'inquiries' => $this->inquiries->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
            'statuses' => InquiryStatus::cases(),
        ]);
    }

    public function show(Inquiry $inquiry): View
    {
        $this->authorize('view', $inquiry);

        return view('inquiries.show', [
            'inquiry' => $inquiry->load(['customerAccount', 'product']),
            'statuses' => InquiryStatus::cases(),
        ]);
    }

    public function updateStatus(UpdateInquiryStatusRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $this->inquiries->updateStatus($inquiry, InquiryStatus::from($request->validated('status')));

        return redirect()->route('inquiries.show', $inquiry)->with('success', 'Inquiry status updated successfully.');
    }
}
