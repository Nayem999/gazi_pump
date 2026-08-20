<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\Brochure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BrochureController extends Controller
{
    public function index(): View
    {
        return view('portal.brochures.index', [
            'brochures' => Cache::rememberForever(
                Brochure::PORTAL_INDEX_CACHE_KEY,
                fn () => Brochure::where('is_published', true)->orderBy('title')->get(),
            ),
        ]);
    }

    public function download(Brochure $brochure): StreamedResponse
    {
        abort_unless($brochure->is_published, 404);
        abort_unless(Storage::disk('public')->exists($brochure->file), 404);

        return Storage::disk('public')->download($brochure->file, $brochure->title.'.pdf');
    }
}
