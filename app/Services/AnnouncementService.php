<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AnnouncementAudience;
use App\Models\Announcement;
use App\Models\CustomerAccount;
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
     * Recipients are either internal Users or portal CustomerAccounts —
     * both use the Notifiable trait, so a heterogeneous collection works
     * fine with Notification::send() even though only one audience type
     * is ever resolved per announcement.
     *
     * @param  array<string, mixed>  $data
     * @return Collection<int, User|CustomerAccount>
     */
    private function resolveRecipients(array $data): Collection
    {
        return match (AnnouncementAudience::from($data['audience'])) {
            AnnouncementAudience::All => User::where('status', true)->get(),
            AnnouncementAudience::Role => User::role($data['audience_role'])->get(),
            AnnouncementAudience::Territory => User::whereHas(
                'territories', fn ($q) => $q->where('territories.id', $data['audience_territory_id'])
            )->where('status', true)->get(),
            AnnouncementAudience::User => User::where('id', $data['audience_user_id'])->get(),
            // Only CustomerAccounts actually have a login/inbox — a Dealer
            // CRM record with no portal account can't receive an in-app
            // notification, so "all dealers" means every linked account.
            AnnouncementAudience::AllDealers => CustomerAccount::whereNotNull('dealer_id')->get(),
            AnnouncementAudience::Dealer => CustomerAccount::where('dealer_id', $data['audience_dealer_id'])->get(),
        };
    }
}
