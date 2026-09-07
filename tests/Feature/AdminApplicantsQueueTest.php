<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Livewire\Admin\ApplicantsTable;
use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminApplicantsQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicants_queue_lists_pending_applications_in_fifo_order(): void
    {
        $admin = Admin::factory()->create();

        $oldest = Applicant::factory()->create([
            'application_id' => '000001',
            'status' => ApplicantStatus::Pending,
            'full_name' => 'Oldest Applicant',
            'created_at' => now()->subDays(2),
        ]);

        $newest = Applicant::factory()->create([
            'application_id' => '000002',
            'status' => ApplicantStatus::Pending,
            'full_name' => 'Newest Applicant',
            'created_at' => now(),
        ]);

        Applicant::factory()->approved()->create(['full_name' => 'Approved Person']);
        Applicant::factory()->rejected()->create(['full_name' => 'Rejected Person']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'));

        $response->assertOk()
            ->assertSee('New Applicants Queue')
            ->assertSee('Application ID')
            ->assertSee('000001')
            ->assertSee('000002')
            ->assertSee('Oldest Applicant')
            ->assertSee('Newest Applicant')
            ->assertDontSee('Approved Person')
            ->assertDontSee('Rejected Person')
            ->assertSee('View Application');

        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, 'Newest Applicant'),
            strpos($content, 'Oldest Applicant'),
        );
    }

    public function test_applicants_queue_search_filters_by_id_name_email_and_barangay(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->create([
            'application_id' => '000111',
            'status' => ApplicantStatus::Pending,
            'full_name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'barangay' => 'Poblacion',
        ]);

        Applicant::factory()->create([
            'application_id' => '000222',
            'status' => ApplicantStatus::Pending,
            'full_name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'barangay' => 'San Jose',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantsTable::class)
            ->set('search', 'Maria')
            ->assertSee('Maria Santos')
            ->assertDontSee('Juan Dela Cruz');

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantsTable::class)
            ->set('search', '000111')
            ->assertSee('Maria Santos')
            ->assertDontSee('Juan Dela Cruz');

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantsTable::class)
            ->set('search', 'maria@example.com')
            ->assertSee('Maria Santos')
            ->assertDontSee('Juan Dela Cruz');

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantsTable::class)
            ->set('search', 'Poblacion')
            ->assertSee('Maria Santos')
            ->assertDontSee('Juan Dela Cruz');
    }

    public function test_new_applicants_queue_highlights_names_matching_finalized_or_delivered(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->approved()->create([
            'full_name' => 'JUAN D. CRUZ',
            'rejection_reason' => null,
        ]);

        Applicant::factory()->approved()->create([
            'full_name' => 'DELIVERED PERSON',
            'rejection_reason' => Applicant::CARD_DELIVERED_REASON,
        ]);

        Applicant::factory()->create([
            'status' => ApplicantStatus::Pending,
            'full_name' => 'JUAN D. CRUZ',
        ]);

        Applicant::factory()->create([
            'status' => ApplicantStatus::Pending,
            'full_name' => 'DELIVERED PERSON',
        ]);

        Applicant::factory()->create([
            'status' => ApplicantStatus::Pending,
            'full_name' => 'UNIQUE PENDING',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantsTable::class)
            ->assertSee('Duplicate full name detected')
            ->assertSee('Duplicate name')
            ->assertSee('JUAN D. CRUZ')
            ->assertSee('DELIVERED PERSON')
            ->assertSee('UNIQUE PENDING');
    }

    public function test_guest_cannot_access_applicants_queue(): void
    {
        $this->get(route('admin.applications.index'))
            ->assertRedirect(route('admin.login'));
    }
}
