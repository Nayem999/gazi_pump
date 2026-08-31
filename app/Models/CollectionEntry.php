<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\ChequeStatus;
use App\Enums\PaymentMethod;
use Database\Factories\CollectionEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionEntry extends BaseModel
{
    /** @use HasFactory<CollectionEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dealer_id',
        'collection_date',
        'amount',
        'payment_method',
        'reference_no',
        'cheque_image',
        'cheque_status',
        'otp_verified_at',
        'remarks',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'collection_date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'cheque_status' => ChequeStatus::class,
            'otp_verified_at' => 'datetime',
            'status' => ApprovalStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function chequeImageUrl(): ?string
    {
        return $this->cheque_image ? asset('storage/'.$this->cheque_image) : null;
    }

    /**
     * A local filesystem path to the cheque image, for PDF generation —
     * DomPDF's remote-file fetching is disabled (config/dompdf.php), so a
     * public asset() URL can't be embedded, only a path within its chroot.
     */
    public function chequeImagePath(): ?string
    {
        if (! $this->cheque_image) {
            return null;
        }

        $path = storage_path('app/public/'.$this->cheque_image);

        return is_file($path) ? $path : null;
    }

    /**
     * Restricts to what the viewer is allowed to see — same shape as
     * Order::scopeVisibleTo() (own records only when Sales Executive is
     * their sole role, own territories' dealers, else unrestricted), via
     * the dealer's territory since CollectionEntry has no territory of its
     * own.
     */
    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        if ($viewer->isSalesExecutiveOnly()) {
            return $query->where('user_id', $viewer->id);
        }

        $territoryIds = $viewer->territories->pluck('id')->all();

        return $territoryIds === []
            ? $query
            : $query->whereHas('dealer', fn ($d) => $d->whereIn('territory_id', $territoryIds));
    }
}
