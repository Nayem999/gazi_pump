<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('faqs.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_access(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('faqs.index'))->assertForbidden();
    }

    public function test_general_manager_can_create_a_faq(): void
    {
        $response = $this->actingAs($this->generalManager())->post(route('faqs.store'), [
            'question' => 'Do you offer installation?',
            'answer' => 'Yes, our service centers offer installation.',
            'sort_order' => 1,
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('faqs.index'));
        $this->assertDatabaseHas('faqs', ['question' => 'Do you offer installation?']);
    }

    public function test_question_and_answer_are_required(): void
    {
        $this->actingAs($this->generalManager())->post(route('faqs.store'), [])
            ->assertSessionHasErrors(['question', 'answer']);
    }

    public function test_general_manager_can_update_delete_and_restore_a_faq(): void
    {
        $manager = $this->generalManager();
        $faq = Faq::factory()->create();

        $this->actingAs($manager)->put(route('faqs.update', $faq), [
            'question' => 'Updated question?',
            'answer' => $faq->answer,
            'is_published' => '1',
        ])->assertRedirect(route('faqs.index'));
        $this->assertDatabaseHas('faqs', ['id' => $faq->id, 'question' => 'Updated question?']);

        $this->actingAs($manager)->delete(route('faqs.destroy', $faq))->assertRedirect(route('faqs.index'));
        $this->assertSoftDeleted('faqs', ['id' => $faq->id]);

        $this->actingAs($manager)->post(route('faqs.restore', $faq->id))->assertRedirect(route('faqs.index'));
        $this->assertDatabaseHas('faqs', ['id' => $faq->id, 'deleted_at' => null]);
    }

    public function test_toggle_status_flips_is_published(): void
    {
        $manager = $this->generalManager();
        $faq = Faq::factory()->create(['is_published' => true]);

        $this->actingAs($manager)->patch(route('faqs.toggle-status', $faq))->assertRedirect();

        $this->assertDatabaseHas('faqs', ['id' => $faq->id, 'is_published' => false]);
    }
}
