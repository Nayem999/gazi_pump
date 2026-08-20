<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brochure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Also writes a minimal valid PDF to the public disk for each seeded row,
 * so the portal's download link works right after a fresh seed instead of
 * pointing at a database path with no real file behind it.
 */
class BrochureSeeder extends Seeder
{
    private const MINIMAL_PDF = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]>>endobj\nxref\n0 4\n0000000000 65535 f \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n0\n%%EOF";

    public function run(): void
    {
        Brochure::factory()->count(5)->create()->each(function (Brochure $brochure) {
            Storage::disk('public')->put($brochure->file, self::MINIMAL_PDF);
        });
    }
}
