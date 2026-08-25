<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\HolidayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends BaseModel
{
    /** @use HasFactory<HolidayFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'status' => 'boolean',
        ];
    }
}
