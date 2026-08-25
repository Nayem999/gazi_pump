<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $dealer = $request->user('customer')->resolveCustomer();

        $payments = $dealer
            ? $dealer->collectionEntries()->latest('collection_date')->paginate(10)
            : new LengthAwarePaginator([], 0, 10);

        return view('portal.payments.index', ['payments' => $payments]);
    }
}
