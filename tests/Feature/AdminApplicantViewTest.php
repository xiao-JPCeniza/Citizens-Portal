<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminApplicantViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_applicant_details(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'application_id' => '000123',
            'full_name' => 'Juan Dela Cruz',
            'birthday' => '1990-05-15',
            'email' => 'juan@example.com',
            'gcash_number' => '09171234567',
            'barangay' => 'Tankulan',
            'address' => 'Purok 1, Tankulan',
            'blood_type' => 'O+',
            'emergency_contact_person' => 'Maria Dela Cruz',
            'emergency_contact_number' => '09179876543',
            'passport_photo' => 'applicants/000123.jpg',
            'gcash_screenshot' => 'applicants/000123-gcash.jpg',
        ]);

        Storage::disk('local')->put('applicants/000123.jpg', 'fake-image');
        Storage::disk('local')->put('applicants/000123-gcash.jpg', 'fake-image');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.show', $applicant))
            ->assertOk()
            ->assertSee('Review Application')
            ->assertSee('000123')
            ->assertSee('Juan Dela Cruz')
            ->assertSee('May 15, 1990')
            ->assertSee('juan@example.com')
            ->assertSee('09171234567')
            ->assertSee('Tankulan')
            ->assertSee('Purok 1, Tankulan')
            ->assertSee('O+')
            ->assertSee('Maria Dela Cruz')
            ->assertSee('09179876543')
            ->assertSee('Passport Photo Preview')
            ->assertSee('GCash Screenshot Preview');

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Application Viewed',
        ]);
    }

    public function test_guest_cannot_view_applicant_details(): void
    {
        $applicant = Applicant::factory()->create();

        $this->get(route('admin.applications.show', $applicant))
            ->assertRedirect(route('admin.login'));
    }
}
