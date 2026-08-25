<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use Database\Factories\CollectionEntryFactory;
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
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'collection_date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
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
}
