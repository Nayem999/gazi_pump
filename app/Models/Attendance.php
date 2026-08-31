<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Models\Concerns\HasVisibilityScope;
use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends BaseModel
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    use HasVisibilityScope;

    protected $fillable = [
        'user_id',
        'date',
        'check_in_at',
        'check_in_lat',
        'check_in_lng',
        'check_in_photo',
        'check_out_at',
        'check_out_lat',
        'check_out_lng',
        'check_out_photo',
        'status',
        'late_minutes',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'check_in_at' => 'datetime',
            'check_in_lat' => 'decimal:7',
            'check_in_lng' => 'decimal:7',
            'check_out_at' => 'datetime',
            'check_out_lat' => 'decimal:7',
            'check_out_lng' => 'decimal:7',
            'status' => AttendanceStatus::class,
            'late_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCheckedOut(): bool
    {
        return $this->check_out_at !== null;
    }

    public function checkInPhotoUrl(): ?string
    {
        return $this->check_in_photo ? asset('storage/'.$this->check_in_photo) : null;
    }

    public function checkOutPhotoUrl(): ?string
    {
        return $this->check_out_photo ? asset('storage/'.$this->check_out_photo) : null;
    }
}
