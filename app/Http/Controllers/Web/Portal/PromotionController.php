<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Contracts\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        return view('portal.promotions.index', [
            'promotions' => Promotion::where('is_active', true)->latest()->paginate(9),
        ]);
    }
}
