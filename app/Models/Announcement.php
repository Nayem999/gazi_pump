<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AnnouncementAudience;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends BaseModel
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'audience',
        'audience_role',
        'audience_territory_id',
        'audience_user_id',
        'sent_by',
        'recipient_count',
    ];

    protected function casts(): array
    {
        return [
            'audience' => AnnouncementAudience::class,
            'recipient_count' => 'integer',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class, 'audience_territory_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audience_user_id');
    }

    public function audienceLabel(): string
    {
        return match ($this->audience) {
            AnnouncementAudience::All => 'Everyone',
            AnnouncementAudience::Role => $this->audience_role ?? 'Role',
            AnnouncementAudience::Territory => $this->territory?->name ?? 'Territory',
            AnnouncementAudience::User => $this->targetUser?->name ?? 'User',
        };
    }
}
