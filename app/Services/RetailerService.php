<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\RetailerRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class RetailerService extends BaseCrudService
{
    public function __construct(private readonly RetailerRepositoryInterface $retailers)
    {
        parent::__construct($retailers);
    }

    /**
     * @param  array{search?: string, dealer_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->retailers->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): Model
    {
        $data['status'] ??= true;

        if ($image) {
            $data['image'] = $image->store('retailers', 'public');
        }

        return parent::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $retailer, array $data, ?UploadedFile $image = null): Model
    {
        if ($image) {
            if ($retailer->image) {
                Storage::disk('public')->delete($retailer->image);
            }
            $data['image'] = $image->store('retailers', 'public');
        }

        return parent::update($retailer, $data);
    }
}
