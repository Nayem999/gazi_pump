<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\NewsRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class NewsService extends BaseCrudService
{
    public function __construct(private readonly NewsRepositoryInterface $news)
    {
        parent::__construct($news);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->news->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $coverImage = null): Model
    {
        $data['is_published'] ??= true;
        $data['published_at'] ??= now();

        if ($coverImage) {
            $data['cover_image'] = $coverImage->store('news', 'public');
        }

        return parent::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $news, array $data, ?UploadedFile $coverImage = null): Model
    {
        if ($coverImage) {
            if ($news->cover_image) {
                Storage::disk('public')->delete($news->cover_image);
            }
            $data['cover_image'] = $coverImage->store('news', 'public');
        }

        return parent::update($news, $data);
    }
}
