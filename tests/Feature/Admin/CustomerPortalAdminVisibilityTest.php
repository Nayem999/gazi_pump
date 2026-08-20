<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\InquiryStatus;
use App\Enums\VisitRequestStatus;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\VisitRequest;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalAdminVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function generalManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('General Manager');

        return $user;
    }

    private function territoryManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Territory Manager');

        return $user;
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    public function test_guest_is_redirected_to_login_for_inquiries(): void
    {
        $this->get(route('inquiries.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access_to_inquiries_or_visit_requests(): void
    {
        $executive = $this->executive();

        $this->actingAs($executive)->get(route('inquiries.index'))->assertForbidden();
        $this->actingAs($executive)->get(route('visit-requests.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_inquiries_list_and_detail(): void
    {
        $manager = $this->generalManager();
        $inquiry = Inquiry::factory()->create();

        $this->actingAs($manager)->get(route('inquiries.index'))->assertOk();
        $this->actingAs($manager)->get(route('inquiries.show', $inquiry))->assertOk();
    }

    public function test_general_manager_can_update_inquiry_status(): void
    {
        $manager = $this->generalManager();
        $inquiry = Inquiry::factory()->create(['status' => InquiryStatus::New]);

        $this->actingAs($manager)->put(route('inquiries.update-status', $inquiry), [
            'status' => InquiryStatus::Responded->value,
        ])->assertRedirect(route('inquiries.show', $inquiry));

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => InquiryStatus::Responded->value]);
    }

    public function test_territory_manager_can_view_but_not_update_inquiry_status(): void
    {
        $manager = $this->territoryManager();
        $inquiry = Inquiry::factory()->create(['status' => InquiryStatus::New]);

        $this->actingAs($manager)->get(route('inquiries.index'))->assertOk();

        $this->actingAs($manager)->put(route('inquiries.update-status', $inquiry), [
            'status' => InquiryStatus::Closed->value,
        ])->assertForbidden();
    }

    public function test_general_manager_can_view_visit_requests_list_and_detail(): void
    {
        $manager = $this->generalManager();
        $visitRequest = VisitRequest::factory()->create();

        $this->actingAs($manager)->get(route('visit-requests.index'))->assertOk();
        $this->actingAs($manager)->get(route('visit-requests.show', $visitRequest))->assertOk();
    }

    public function test_general_manager_can_update_visit_request_status(): void
    {
        $manager = $this->generalManager();
        $visitRequest = VisitRequest::factory()->create(['status' => VisitRequestStatus::Pending]);

        $this->actingAs($manager)->put(route('visit-requests.update-status', $visitRequest), [
            'status' => VisitRequestStatus::Scheduled->value,
        ])->assertRedirect(route('visit-requests.show', $visitRequest));

        $this->assertDatabaseHas('visit_requests', ['id' => $visitRequest->id, 'status' => VisitRequestStatus::Scheduled->value]);
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        $manager = $this->generalManager();
        $inquiry = Inquiry::factory()->create();

        $this->actingAs($manager)->put(route('inquiries.update-status', $inquiry), [
            'status' => 'not-a-real-status',
        ])->assertSessionHasErrors('status');
    }
}
