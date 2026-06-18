<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Livewire\Admin\ArchiveTable;
use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_lists_rejected_applications_only(): void
    {
        $admin = Admin::factory()->create();

        $rejected = Applicant::factory()->rejected()->create([
            'full_name' => 'Archived Applicant',
            'rejection_reason' => 'Invalid Passport Photo',
            'verified_at' => now()->subDay(),
        ]);

        Applicant::factory()->create([
            'full_name' => 'Pending Applicant',
            'status' => ApplicantStatus::Pending,
        ]);

        Applicant::factory()->approved()->create([
            'full_name' => 'Approved Applicant',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.archive.index'))
            ->assertOk()
            ->assertSee('Archive')
            ->assertSee('Archived Applicant')
            ->assertSee('Invalid Passport Photo')
            ->assertSee('Review History')
            ->assertDontSee('Pending Applicant')
            ->assertDontSee('Approved Applicant');
    }

    public function test_archive_search_filters_by_reference_name_and_reason(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->rejected()->create([
            'full_name' => 'Maria Santos',
            'rejection_reason' => 'Duplicate Application',
        ]);

        Applicant::factory()->rejected()->create([
            'full_name' => 'Juan Dela Cruz',
            'rejection_reason' => 'Incomplete Information',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ArchiveTable::class)
            ->set('search', 'Maria')
            ->assertSee('Maria Santos')
            ->assertDontSee('Juan Dela Cruz');

        Livewire::actingAs($admin, 'admin')
            ->test(ArchiveTable::class)
            ->set('search', 'Duplicate')
            ->assertSee('Maria Santos')
            ->assertDontSee('Juan Dela Cruz');
    }

    public function test_admin_can_review_rejection_history_from_archive(): void
    {
        $verifier = Admin::factory()->create(['name' => 'Verifier Admin']);
        $admin = Admin::factory()->create();

        $applicant = Applicant::factory()->rejected()->create([
            'rejection_reason' => 'Invalid GCash Screenshot: Blurry image.',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.show', $applicant))
            ->assertOk()
            ->assertSee('Rejection History')
            ->assertSee('Invalid GCash Screenshot: Blurry image.')
            ->assertSee('Verifier Admin')
            ->assertSee('Back to Archive');
    }

    public function test_guest_cannot_access_archive(): void
    {
        $this->get(route('admin.archive.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_rejected_applications_do_not_appear_in_verification_queue(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->rejected()->create(['full_name' => 'Should Be Archived']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertDontSee('Should Be Archived');
    }
}
