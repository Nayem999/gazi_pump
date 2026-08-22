<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\ServiceCenter;
use App\Models\Territory;
use Illuminate\Contracts\View\View;

class DealerLocatorController extends Controller
{
    public function index(): View
    {
        $dealers = Dealer::where('status', true)
            ->whereNotNull('gps_lat')
            ->whereNotNull('gps_lng')
            ->with('territory')
            ->get();

        $serviceCenters = ServiceCenter::where('is_active', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get();

        // Only the territories that actually have a dealer in them — same
        // "fill in whatever's in view once zoomed in" idea as the admin
        // Territory Map, scoped down to a public-safe set of columns (no
        // manager/sales data, just the shape + name).
        $territories = Territory::query()
            ->whereIn('id', $dealers->pluck('territory_id')->filter()->unique())
            ->whereNotNull('boundary')
            ->select(['id', 'name', 'code', 'center_lat', 'center_lng', 'boundary'])
            ->get();

        return view('portal.dealer-locator', [
            'dealers' => $dealers,
            'serviceCenters' => $serviceCenters,
            'territories' => $territories,
        ]);
    }
}
