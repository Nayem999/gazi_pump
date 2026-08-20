<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('portal.home', [
            'featuredProducts' => Product::where('status', true)->latest()->limit(6)->get(),
            'latestNews' => News::where('is_published', true)->latest('published_at')->limit(3)->get(),
            'activePromotions' => Promotion::where('is_active', true)->latest()->limit(3)->get(),
        ]);
    }

    public function about(): View
    {
        return view('portal.about');
    }

    public function warranty(): View
    {
        return view('portal.warranty');
    }
}
