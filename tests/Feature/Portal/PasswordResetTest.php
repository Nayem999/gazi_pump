<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\CustomerAccount;
use App\Notifications\CustomerResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('portal.password.request'))->assertOk();
    }

    public function test_requesting_a_reset_link_for_a_known_email_sends_a_notification(): void
    {
        Notification::fake();

        $account = CustomerAccount::factory()->create(['email' => 'customer@example.com']);

        $this->post(route('portal.password.email'), ['email' => 'customer@example.com'])
            ->assertRedirect(route('portal.password.request'))
            ->assertSessionHas('success');

        Notification::assertSentTo($account, CustomerResetPasswordNotification::class);
    }

    public function test_requesting_a_reset_link_for_an_unknown_email_shows_the_same_generic_message(): void
    {
        Notification::fake();

        $this->post(route('portal.password.email'), ['email' => 'nobody@example.com'])
            ->assertRedirect(route('portal.password.request'))
            ->assertSessionHas('success');

        Notification::assertNothingSent();
    }

    public function test_reset_password_page_renders_with_the_token_and_email_prefilled(): void
    {
        $this->get(route('portal.password.reset', ['token' => 'a-token', 'email' => 'customer@example.com']))
            ->assertOk()
            ->assertViewHas('token', 'a-token')
            ->assertSee('customer@example.com');
    }

    public function test_can_reset_password_with_a_valid_token(): void
    {
        $account = CustomerAccount::factory()->create(['email' => 'customer@example.com']);
        $token = Password::broker('customers')->createToken($account);

        $this->post(route('portal.password.update'), [
            'token' => $token,
            'email' => 'customer@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
            ->assertRedirect(route('portal.login'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpassword123', $account->refresh()->password));
    }

    public function test_reset_fails_with_an_invalid_token(): void
    {
        CustomerAccount::factory()->create(['email' => 'customer@example.com']);

        $this->post(route('portal.password.update'), [
            'token' => 'not-a-real-token',
            'email' => 'customer@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors('email');
    }
}
