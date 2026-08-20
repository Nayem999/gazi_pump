<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('portal.faq.index', [
            'faqs' => Cache::rememberForever(
                Faq::PORTAL_INDEX_CACHE_KEY,
                fn () => Faq::where('is_published', true)->orderBy('sort_order')->get(),
            ),
        ]);
    }
}
