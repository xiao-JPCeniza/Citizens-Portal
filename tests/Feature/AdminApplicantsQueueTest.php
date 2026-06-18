<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApplicantsQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicants_queue_lists_pending_applications_in_fifo_order(): void
    {
        $admin = Admin::factory()->create();

        $oldest = Applicant::factory()->create([
            'status' => ApplicantStatus::Pending,
            'full_name' => 'Oldest Applicant',
            'created_at' => now()->subDays(2),
        ]);

        $newest = Applicant::factory()->create([
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

    public function test_guest_cannot_access_applicants_queue(): void
    {
        $this->get(route('admin.applications.index'))
            ->assertRedirect(route('admin.login'));
    }
}
