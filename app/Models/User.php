<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasAudit;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * The single user model shared by the admin/web guard and the Sanctum API
 * guard. Can't extend App\Models\BaseModel (that extends the generic
 * Eloquent Model) because it must extend Authenticatable instead, so the
 * SoftDeletes/HasAudit/LogsActivity trio is composed here directly.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasAudit;
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    /**
     * Pins Spatie's guard resolution to 'web' regardless of which guard
     * (session or Sanctum) actually authenticated the request. Without
     * this, Laravel's Authenticate middleware calls Auth::shouldUse('sanctum')
     * on API requests, which mutates config('auth.defaults.guard') for the
     * rest of the request — and Spatie's Guard::getDefaultName() picks that
     * up, looking for permissions under guard_name 'sanctum' instead of
     * 'web' (where every permission in this app is actually seeded), making
     * every permission check silently fail on API-only requests.
     *
     * @var string
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'phone',
        'photo',
        'designation',
        'date_of_birth',
        'manager_id',
        'sales_team_id',
        'territory_id',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'date_of_birth' => 'date:Y-m-d',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->useLogName('users');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function salesTeam(): BelongsTo
    {
        return $this->belongsTo(SalesTeam::class);
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function isBirthdayToday(): bool
    {
        return $this->date_of_birth !== null && $this->date_of_birth->isBirthday();
    }
}
