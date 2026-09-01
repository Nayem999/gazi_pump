<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VisitPlanStatus;
use App\Helpers\DistanceCalculator;
use App\Models\Dealer;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPlan;
use App\Repositories\Contracts\VisitRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class VisitService extends BaseCrudService
{
    public function __construct(private readonly VisitRepositoryInterface $visits)
    {
        parent::__construct($visits);
    }

    /**
     * @param  array{search?: string, user_id?: string, dealer_id?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->visits->paginateWithFilters($filters, $perPage);
    }

    /**
     * Every path that records a visit — mobile check-in, and the admin
     * create/edit form for backfilling a visit an admin already knows
     * happened — should mark a matching Visit Plan as fulfilled, whether or
     * not the visit was explicitly linked to one.
     */
    public function create(array $data): Model
    {
        $visit = parent::create($data);

        $this->completeMatchingVisitPlan($visit);

        return $visit;
    }

    public function update(Model $model, array $data): Model
    {
        $visit = parent::update($model, $data);

        $this->completeMatchingVisitPlan($visit);

        return $visit;
    }

    public function checkIn(User $user, int $dealerId, ?int $visitPlanId, float $lat, float $lng, UploadedFile $photo): Visit
    {
        if ($this->visits->findOpenVisitForUser($user->id)) {
            throw ValidationException::withMessages([
                'check_in' => 'You still have an open visit — check out first.',
            ]);
        }

        $dealer = Dealer::findOrFail($dealerId);
        [$verified, $distanceMeters] = $this->verifyGpsProximity($dealer, $lat, $lng);

        $visit = $this->create([
            'visit_plan_id' => $visitPlanId,
            'user_id' => $user->id,
            'dealer_id' => $dealerId,
            'check_in_at' => Carbon::now(),
            'check_in_lat' => $lat,
            'check_in_lng' => $lng,
            'check_in_photo' => $photo->store('visits', 'public'),
            'is_gps_verified' => $verified,
            'distance_from_dealer_meters' => $distanceMeters,
        ]);

        return $visit;
    }

    public function checkOut(User $user, float $lat, float $lng, ?UploadedFile $photo, string $feedback): Visit
    {
        $visit = $this->visits->findOpenVisitForUser($user->id);

        if (! $visit) {
            throw ValidationException::withMessages([
                'check_out' => 'You do not have an open visit to check out from.',
            ]);
        }

        return $this->update($visit, [
            'check_out_at' => Carbon::now(),
            'check_out_lat' => $lat,
            'check_out_lng' => $lng,
            'check_out_photo' => $photo ? $photo->store('visits', 'public') : $visit->check_out_photo,
            'feedback' => $feedback,
        ]);
    }

    /**
     * Compares a check-in point against the dealer's registered GPS pin.
     * Returns [null, null] when the dealer has no pin on file at all —
     * that's "unknown", not "unverified" (which would wrongly imply the rep
     * checked in at the wrong place).
     *
     * @return array{0: bool|null, 1: float|null}
     */
    public function verifyGpsProximity(Dealer $dealer, float $lat, float $lng): array
    {
        if (! $dealer->hasGps()) {
            return [null, null];
        }

        $distanceMeters = DistanceCalculator::haversineKm(
            (float) $dealer->gps_lat,
            (float) $dealer->gps_lng,
            $lat,
            $lng
        ) * 1000;

        $radius = (int) config('sfa.visits.gps_verification_radius_meters');

        return [$distanceMeters <= $radius, round($distanceMeters, 2)];
    }

    /**
     * An explicit visit_plan_id link is always honoured. Otherwise, fall
     * back to matching on the same executive + dealer + date — this catches
     * a walk-in visit (mobile check-in with no plan selected, or an admin
     * backfilling one) that happens to fulfil a plan nobody linked it to.
     * Only a still-Planned plan is touched, so a Cancelled one is never
     * silently resurrected.
     */
    private function completeMatchingVisitPlan(Visit $visit): void
    {
        if ($visit->visit_plan_id) {
            VisitPlan::whereKey($visit->visit_plan_id)->update(['status' => VisitPlanStatus::Completed->value]);

            return;
        }

        if (! $visit->check_in_at) {
            return;
        }

        VisitPlan::query()
            ->where('user_id', $visit->user_id)
            ->where('dealer_id', $visit->dealer_id)
            ->where('planned_date', $visit->check_in_at->toDateString())
            ->where('status', VisitPlanStatus::Planned->value)
            ->update(['status' => VisitPlanStatus::Completed->value]);
    }
}
