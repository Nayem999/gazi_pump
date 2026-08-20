<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class PromotionService extends BaseCrudService
{
    public function __construct(private readonly PromotionRepositoryInterface $promotions)
    {
        parent::__construct($promotions);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->promotions->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): Model
    {
        $data['is_active'] ??= true;

        if ($image) {
            $data['image'] = $image->store('promotions', 'public');
        }

        return parent::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $promotion, array $data, ?UploadedFile $image = null): Model
    {
        if ($image) {
            if ($promotion->image) {
                Storage::disk('public')->delete($promotion->image);
            }
            $data['image'] = $image->store('promotions', 'public');
        }

        return parent::update($promotion, $data);
    }
}
