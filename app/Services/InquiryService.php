<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Repositories\Contracts\InquiryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class InquiryService extends BaseCrudService
{
    public function __construct(private readonly InquiryRepositoryInterface $inquiries)
    {
        parent::__construct($inquiries);
    }

    /**
     * @param  array{search?: string, status?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->inquiries->paginateWithFilters($filters, $perPage);
    }

    public function updateStatus(Inquiry $inquiry, InquiryStatus $status): Inquiry
    {
        /** @var Inquiry */
        return $this->inquiries->update($inquiry, ['status' => $status]);
    }
}
