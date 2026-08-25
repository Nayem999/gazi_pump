<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ProductService extends BaseCrudService
{
    public function __construct(private readonly ProductRepositoryInterface $products)
    {
        parent::__construct($products);
    }

    /**
     * @param  array{search?: string, category_id?: string, sales_team_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return $this->products->paginateWithFilters($filters, $perPage, $viewer);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $image = null): Model
    {
        $data['status'] ??= true;

        if ($image) {
            $data['image'] = $image->store('products', 'public');
        }

        return parent::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $product, array $data, ?UploadedFile $image = null): Model
    {
        if ($image) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $image->store('products', 'public');
        }

        return parent::update($product, $data);
    }
}
