<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VisitRequestStatus;
use Database\Factories\VisitRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitRequest extends BaseModel
{
    /** @use HasFactory<VisitRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_account_id',
        'preferred_date',
        'address',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date:Y-m-d',
            'status' => VisitRequestStatus::class,
        ];
    }

    public function customerAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerAccount::class);
    }
}
