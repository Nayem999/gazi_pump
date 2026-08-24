<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Territory;
use App\Repositories\Contracts\DealerRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class DealerService extends BaseCrudService
{
    public function __construct(private readonly DealerRepositoryInterface $dealers)
    {
        parent::__construct($dealers);
    }

    /**
     * @param  array{search?: string, type?: string, territory_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->dealers->paginateWithFilters($filters, $perPage);
    }

    /**
     * The mobile API's store request omits `status` entirely (new field
     * registrations are always active), which would otherwise leave it unset
     * on the in-memory model post-insert — Eloquent doesn't re-hydrate
     * unset attributes from the column's DB default after a fresh create().
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): Model
    {
        $data['status'] ??= true;

        if ($image) {
            $data['image'] = $image->store('dealers', 'public');
        }

        return parent::create($this->withGeoFromTerritory($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data, ?UploadedFile $image = null): Model
    {
        if ($image) {
            if ($model->image) {
                Storage::disk('public')->delete($model->image);
            }
            $data['image'] = $image->store('dealers', 'public');
        }

        return parent::update($model, $this->withGeoFromTerritory($data));
    }

    /**
     * A dealer's division/district/thana are never chosen independently —
     * the admin/mobile-app UI only narrows the Territory dropdown through
     * them. Whatever Territory ends up selected is the single source of
     * truth, copied here so a dealer's geographic fields can never drift
     * out of sync with its Territory's own hierarchy.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withGeoFromTerritory(array $data): array
    {
        if (! array_key_exists('territory_id', $data)) {
            return $data;
        }

        if ($data['territory_id'] === null) {
            return [...$data, 'division_id' => null, 'district_id' => null, 'thana_id' => null];
        }

        $territory = Territory::find($data['territory_id']);

        return [
            ...$data,
            'division_id' => $territory?->division_id,
            'district_id' => $territory?->district_id,
            'thana_id' => $territory?->thana_id,
        ];
    }
}
