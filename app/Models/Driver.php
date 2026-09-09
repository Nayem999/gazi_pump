<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DriverFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends BaseModel
{
    /** @use HasFactory<DriverFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'license_number',
        'phone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
