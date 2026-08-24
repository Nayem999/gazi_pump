<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_any_authenticated_role_can_view_their_own_profile(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee($executive->name);
    }

    public function test_a_user_can_update_their_own_name_email_and_phone(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '01900000000',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '01900000000',
        ]);
    }

    public function test_a_user_can_change_their_own_password(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }

    public function test_leaving_password_blank_keeps_the_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OriginalPassword123')]);
        $user->assignRole('Super Admin');

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
        ])->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('OriginalPassword123', $user->fresh()->password));
    }

    public function test_email_must_be_unique_among_other_users(): void
    {
        $other = User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ])->assertSessionHasErrors('email');
    }

    public function test_updating_the_profile_does_not_alter_the_users_territories(): void
    {
        $territory = Territory::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');
        $user->territories()->sync([$territory->id]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame([$territory->id], $user->territories()->pluck('territories.id')->all());
    }
}
