<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\ServiceCenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class ServiceCenterController extends Controller
{
    public function index(): View
    {
        return view('portal.service-centers.index', [
            'serviceCenters' => Cache::rememberForever(
                ServiceCenter::PORTAL_INDEX_CACHE_KEY,
                fn () => ServiceCenter::where('is_active', true)->orderBy('name')->get(),
            ),
        ]);
    }
}
