<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\VisitPlanStatus;
use App\Helpers\DistanceCalculator;
use App\Models\Customer;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPlan;
use App\Repositories\Contracts\VisitRepositoryInterface;
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
     * @param  array{search?: string, user_id?: string, customer_id?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->visits->paginateWithFilters($filters, $perPage);
    }

    public function checkIn(User $user, int $customerId, ?int $visitPlanId, float $lat, float $lng, UploadedFile $photo): Visit
    {
        if ($this->visits->findOpenVisitForUser($user->id)) {
            throw ValidationException::withMessages([
                'check_in' => 'You still have an open visit — check out first.',
            ]);
        }

        $customer = Customer::findOrFail($customerId);
        [$verified, $distanceMeters] = $this->verifyGpsProximity($customer, $lat, $lng);

        $visit = $this->create([
            'visit_plan_id' => $visitPlanId,
            'user_id' => $user->id,
            'customer_id' => $customerId,
            'check_in_at' => Carbon::now(),
            'check_in_lat' => $lat,
            'check_in_lng' => $lng,
            'check_in_photo' => $photo->store('visits', 'public'),
            'is_gps_verified' => $verified,
            'distance_from_customer_meters' => $distanceMeters,
        ]);

        if ($visitPlanId) {
            VisitPlan::whereKey($visitPlanId)->update(['status' => VisitPlanStatus::Completed->value]);
        }

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
     * Compares a check-in point against the customer's registered GPS pin.
     * Returns [null, null] when the customer has no pin on file at all —
     * that's "unknown", not "unverified" (which would wrongly imply the rep
     * checked in at the wrong place).
     *
     * @return array{0: bool|null, 1: float|null}
     */
    public function verifyGpsProximity(Customer $customer, float $lat, float $lng): array
    {
        if (! $customer->hasGps()) {
            return [null, null];
        }

        $distanceMeters = DistanceCalculator::haversineKm(
            (float) $customer->gps_lat,
            (float) $customer->gps_lng,
            $lat,
            $lng
        ) * 1000;

        $radius = (int) config('sfa.visits.gps_verification_radius_meters');

        return [$distanceMeters <= $radius, round($distanceMeters, 2)];
    }
}
