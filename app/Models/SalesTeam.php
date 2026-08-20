<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SalesTeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesTeam extends BaseModel
{
    /** @use HasFactory<SalesTeamFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'sales_team_id');
    }
}
