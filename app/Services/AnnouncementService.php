<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AnnouncementAudience;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use App\Repositories\Contracts\AnnouncementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AnnouncementService extends BaseCrudService
{
    public function __construct(private readonly AnnouncementRepositoryInterface $announcements)
    {
        parent::__construct($announcements);
    }

    /**
     * @param  array{search?: string, audience?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->announcements->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function send(User $sender, array $data): Announcement
    {
        return DB::transaction(function () use ($sender, $data) {
            $recipients = $this->resolveRecipients($data);

            /** @var Announcement $announcement */
            $announcement = $this->announcements->create([
                ...$data,
                'sent_by' => $sender->id,
                'recipient_count' => $recipients->count(),
            ]);

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new AnnouncementNotification($announcement));
            }

            return $announcement;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, User>
     */
    private function resolveRecipients(array $data): Collection
    {
        return match (AnnouncementAudience::from($data['audience'])) {
            AnnouncementAudience::All => User::where('status', true)->get(),
            AnnouncementAudience::Role => User::role($data['audience_role'])->get(),
            AnnouncementAudience::Territory => User::where('territory_id', $data['audience_territory_id'])->where('status', true)->get(),
            AnnouncementAudience::User => User::where('id', $data['audience_user_id'])->get(),
        };
    }
}
