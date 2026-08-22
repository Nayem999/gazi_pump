<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\CustomerResetPasswordNotification;
use Database\Factories\CustomerAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * The customer portal's login identity, authenticated via the 'customer'
 * guard — deliberately separate from App\Models\Customer (the CRM record
 * sales staff manage) and from App\Models\User (staff/admin accounts).
 * No HasAudit/LogsActivity here: accounts are self-registered, so there's
 * no internal "creator" to stamp, and per-customer activity noise isn't
 * useful to the staff-facing activity log.
 */
class CustomerAccount extends Authenticatable
{
    /** @use HasFactory<CustomerAccountFactory> */
    use HasFactory;

    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'dealer_id',
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    /**
     * Self-registered accounts aren't linked to a CRM Dealer record at
     * signup (dealer_id starts null) — this resolves the link by email
     * whenever one is missing, and persists it so the match only has to
     * happen once per account. Returns null if no matching Dealer exists.
     */
    public function resolveCustomer(): ?Dealer
    {
        if ($this->dealer_id !== null) {
            return $this->dealer;
        }

        $dealer = Dealer::where('email', $this->email)->first();

        if ($dealer) {
            $this->forceFill(['dealer_id' => $dealer->id])->save();
            $this->setRelation('dealer', $dealer);
        }

        return $dealer;
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function visitRequests(): HasMany
    {
        return $this->hasMany(VisitRequest::class);
    }

    /**
     * Overrides the CanResetPassword trait's default (which sends Laravel's
     * own Illuminate\Auth\Notifications\ResetPassword, pointed at a
     * 'password.reset' route this app doesn't have) so the portal gets its
     * own notification pointed at 'portal.password.reset' instead.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomerResetPasswordNotification($token));
    }
}
