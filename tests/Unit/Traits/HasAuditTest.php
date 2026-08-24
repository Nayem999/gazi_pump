<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use App\Models\CustomerAccount;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for a real bug: HasAudit used to stamp Auth::id()
 * onto created_by/updated_by regardless of which guard authenticated it.
 * created_by has a foreign key to `users`, so when only a CustomerAccount
 * (a different guard/model entirely) was authenticated — e.g. the very
 * first request that lazily creates the Settings row on the customer
 * portal — the insert violated the FK and the request 500'd.
 */
class HasAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_model_while_only_a_customer_account_is_authenticated_does_not_violate_the_created_by_foreign_key(): void
    {
        $account = CustomerAccount::factory()->create();

        $this->actingAs($account, 'customer');

        $settings = Setting::current();

        $this->assertNull($settings->created_by);
    }

    public function test_creating_a_model_while_a_user_is_authenticated_stamps_created_by(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $settings = Setting::current();

        $this->assertSame($user->id, $settings->created_by);
    }
}
