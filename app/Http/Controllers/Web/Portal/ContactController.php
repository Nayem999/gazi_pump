<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreContactInquiryRequest;
use App\Models\Inquiry;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function create(Request $request): View
    {
        return view('portal.contact', [
            'product' => $request->filled('product_id') ? Product::find($request->integer('product_id')) : null,
        ]);
    }

    public function store(StoreContactInquiryRequest $request): RedirectResponse
    {
        Inquiry::create([
            ...$request->validated(),
            'customer_account_id' => Auth::guard('customer')->id(),
            'status' => InquiryStatus::New,
        ]);

        return redirect()->route('portal.contact')->with('success', "Thanks for reaching out! We'll get back to you soon.");
    }
}
