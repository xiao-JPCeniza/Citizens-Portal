<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Livewire\Admin\Login;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_accessible(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Sign in to your account')
            ->assertSee('Email')
            ->assertSee('Password');
    }

    public function test_admin_can_log_in_with_valid_credentials(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@mf.gov.ph',
            'password' => 'password',
            'role' => AdminRole::Administrator->value,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin@mf.gov.ph')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Admin Login',
        ]);
    }

    public function test_admin_cannot_log_in_with_invalid_credentials(): void
    {
        Admin::factory()->create([
            'email' => 'admin@mf.gov.ph',
            'password' => 'password',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin@mf.gov.ph')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest('admin');
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_authenticated_admin_is_redirected_from_login_page(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_admin_can_log_in_with_remember_me(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@mf.gov.ph',
            'password' => 'password',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'admin@mf.gov.ph')
            ->set('password', 'password')
            ->set('remember', true)
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertNotNull($admin->fresh()->remember_token);
    }

    public function test_admin_can_log_out(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Admin Logout',
        ]);
    }
}
