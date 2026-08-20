<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        return view('portal.inquiries.index', [
            'inquiries' => $request->user('customer')->inquiries()->latest()->paginate(10),
        ]);
    }
}
