<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService extends BaseCrudService
{
    public function __construct(private readonly CustomerRepositoryInterface $customers)
    {
        parent::__construct($customers);
    }

    /**
     * @param  array{search?: string, type?: string, territory_id?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->customers->paginateWithFilters($filters, $perPage);
    }

    /**
     * The mobile API's store request omits `status` entirely (new field
     * registrations are always active), which would otherwise leave it unset
     * on the in-memory model post-insert — Eloquent doesn't re-hydrate
     * unset attributes from the column's DB default after a fresh create().
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        $data['status'] ??= true;

        return parent::create($data);
    }
}
