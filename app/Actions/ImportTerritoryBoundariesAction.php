<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Replaces every row in `territories` with the real Union Council (ADM4)
 * boundaries published by geoBoundaries.org (CC BY 3.0 IGO), so the
 * Territory Map shows accurate administrative shapes instead of demo
 * polygons. `shapeID` becomes the unique `code` (union names collide often
 * — Bangladesh has 841 duplicate union names among the 5,160 unions, so
 * `name` alone can't be unique). A plain vertex-average centroid is stored
 * in center_lat/center_lng as a fallback marker position for territories
 * that end up with no computed data point.
 *
 * Existing users/dealers pointing at the deleted rows are set to NULL by
 * their `territory_id` FK's nullOnDelete, not left dangling.
 */
class ImportTerritoryBoundariesAction
{
    private const SOURCE_PATH = 'bgd-adm4-unions.geojson';

    private const CHUNK_SIZE = 500;

    public function __invoke(): int
    {
        $features = $this->readFeatures();

        DB::table('territories')->delete();

        $now = now();
        $imported = 0;

        foreach (array_chunk($features, self::CHUNK_SIZE) as $chunk) {
            $rows = [];

            foreach ($chunk as $feature) {
                $geometry = $feature['geometry'];
                [$lat, $lng] = $this->centroid($geometry);

                $rows[] = [
                    'name' => $feature['properties']['shapeName'],
                    'code' => $feature['properties']['shapeID'],
                    'manager_id' => null,
                    'center_lat' => $lat,
                    'center_lng' => $lng,
                    'boundary' => json_encode($geometry),
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('territories')->insert($rows);
            $imported += count($rows);
        }

        return $imported;
    }

    /**
     * @return array<int, array{properties: array<string, mixed>, geometry: array<string, mixed>}>
     */
    private function readFeatures(): array
    {
        $path = database_path('data/'.self::SOURCE_PATH);

        if (! is_file($path)) {
            throw new RuntimeException("Territory boundary source file not found at {$path}");
        }

        $geoJson = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        return $geoJson['features'];
    }

    /**
     * @param  array{type: string, coordinates: array<mixed>}  $geometry
     * @return array{0: float, 1: float} [lat, lng]
     */
    private function centroid(array $geometry): array
    {
        $ring = $geometry['type'] === 'MultiPolygon'
            ? $geometry['coordinates'][0][0]
            : $geometry['coordinates'][0];

        $count = count($ring);
        $sumLng = 0.0;
        $sumLat = 0.0;

        foreach ($ring as [$lng, $lat]) {
            $sumLng += $lng;
            $sumLat += $lat;
        }

        return [$sumLat / $count, $sumLng / $count];
    }
}
