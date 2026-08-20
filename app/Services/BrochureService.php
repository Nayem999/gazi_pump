<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\BrochureRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class BrochureService extends BaseCrudService
{
    public function __construct(private readonly BrochureRepositoryInterface $brochures)
    {
        parent::__construct($brochures);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->brochures->paginateWithFilters($filters, $perPage);
    }

    /**
     * $file is only optional here to satisfy BaseCrudService::create()'s
     * signature (a PHP override can't add a required parameter) — the
     * controller always passes one, since StoreBrochureRequest requires it.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $file = null, ?UploadedFile $coverImage = null): Model
    {
        $data['is_published'] ??= true;
        $data['file'] = $file?->store('brochures', 'public');

        if ($coverImage) {
            $data['cover_image'] = $coverImage->store('brochures', 'public');
        }

        return parent::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $brochure, array $data, ?UploadedFile $file = null, ?UploadedFile $coverImage = null): Model
    {
        if ($file) {
            Storage::disk('public')->delete($brochure->file);
            $data['file'] = $file->store('brochures', 'public');
        }

        if ($coverImage) {
            if ($brochure->cover_image) {
                Storage::disk('public')->delete($brochure->cover_image);
            }
            $data['cover_image'] = $coverImage->store('brochures', 'public');
        }

        return parent::update($brochure, $data);
    }
}
