<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Since Module 17, the root URL serves the Customer Web Portal home
     * page instead of redirecting to the admin dashboard.
     */
    public function test_the_root_url_renders_the_portal_home_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('portal.home');
    }
}
