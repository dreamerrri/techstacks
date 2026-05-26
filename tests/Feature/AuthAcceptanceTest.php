<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_and_admin_users_can_log_in_successfully()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@company.com',
            'password' => bcrypt('password'),
        ]);

        $hr = User::factory()->hr()->create([
            'email' => 'hr@company.com',
            'password' => bcrypt('password'),
        ]);

        $this->followingRedirects()
            ->post('/login', [
                'email' => 'admin@company.com',
                'password' => 'password',
            ])
            ->assertSee('Admin Dashboard');

        $this->assertAuthenticatedAs($admin);

        auth()->logout();

        $this->followingRedirects()
            ->post('/login', [
                'email' => 'hr@company.com',
                'password' => 'password',
            ])
            ->assertSee('HR Dashboard');

        $this->assertAuthenticatedAs($hr);
    }

    public function test_invalid_credentials_display_proper_error_messages()
    {
        User::factory()->admin()->create([
            'email' => 'admin@company.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@company.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_sessions_are_maintained_securely_after_login()
    {
        $user = User::factory()->employee()->create([
            'email' => 'john@company.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'john@company.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Dashboard');
    }

    public function test_users_are_redirected_based_on_role()
    {
        $hr = User::factory()->hr()->create([
            'email' => 'hr@company.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'hr@company.com',
            'password' => 'password',
        ])->assertRedirect('/hr/dashboard');

        auth()->logout();

        $admin = User::factory()->admin()->create([
            'email' => 'admin@company.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'admin@company.com',
            'password' => 'password',
        ])->assertRedirect('/admin/dashboard');
    }

    public function test_protected_pages_cannot_be_accessed_without_login()
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/hr/dashboard')->assertRedirect('/login');
    }

    public function test_login_page_is_responsive_on_desktop_and_mobile()
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1.0">', false);
        $response->assertSee('@media (max-width: 768px)', false);
    }
}
