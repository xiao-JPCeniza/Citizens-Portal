<?php

namespace Tests\Feature;

use App\Livewire\Admin\FinalizationTable;
use App\Mail\DistributionEventMail;
use App\Models\Admin;
use App\Models\Applicant;
use Database\Seeders\BarangaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDistributionEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BarangaySeeder::class);
    }

    public function test_finalized_page_shows_selection_and_send_email_controls(): void
    {
        $admin = Admin::factory()->create();
        Applicant::factory()->approved()->create(['full_name' => 'Selected Applicant']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.index'))
            ->assertOk()
            ->assertSee('Send Email')
            ->assertSee('Selected Applicant');
    }

    public function test_admin_can_send_distribution_email_to_selected_applicants(): void
    {
        Mail::fake();
        Storage::fake('local');

        $admin = Admin::factory()->create();

        $first = Applicant::factory()->approved()->create([
            'email' => 'first@example.com',
            'full_name' => 'First Applicant',
        ]);

        $second = Applicant::factory()->approved()->create([
            'email' => 'second@example.com',
            'full_name' => 'Second Applicant',
        ]);

        Applicant::factory()->create(['email' => 'pending@example.com']);

        $poster = UploadedFile::fake()->image('poster.jpg', 800, 600);

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->set('selectedApplicants', [(string) $first->id, (string) $second->id])
            ->call('openEmailModal')
            ->assertSet('showEmailModal', true)
            ->set('when', 'June 25, 2026, 9:00 AM')
            ->set('where', 'Municipal Gymnasium, Tankulan')
            ->set('what', 'Citizen ID card distribution')
            ->set('poster_photo', $poster)
            ->call('sendDistributionEmail')
            ->assertHasNoErrors()
            ->assertSet('showEmailModal', false)
            ->assertSet('selectedApplicants', []);

        Mail::assertSent(DistributionEventMail::class, 2);

        Mail::assertSent(DistributionEventMail::class, function (DistributionEventMail $mail) use ($first): bool {
            return $mail->hasTo('first@example.com')
                && $mail->applicant->is($first)
                && $mail->when === 'June 25, 2026, 9:00 AM'
                && $mail->where === 'Municipal Gymnasium, Tankulan'
                && $mail->what === 'Citizen ID card distribution';
        });
    }

    public function test_distribution_email_requires_selected_applicants_and_fields(): void
    {
        $admin = Admin::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->set('showEmailModal', true)
            ->call('sendDistributionEmail')
            ->assertHasErrors([
                'selectedApplicants',
                'when',
                'where',
                'what',
                'poster_photo',
            ]);
    }

    public function test_distribution_email_renders_event_details(): void
    {
        $applicant = new Applicant([
            'full_name' => 'JUAN DELA CRUZ',
            'email' => 'juan@example.com',
        ]);

        $posterPath = UploadedFile::fake()->image('poster.jpg')->store('posters');
        $fullPosterPath = Storage::path($posterPath);

        $html = (new DistributionEventMail(
            $applicant,
            'June 25, 2026, 9:00 AM',
            'Municipal Gymnasium',
            'Citizen ID distribution',
            $fullPosterPath,
        ))->render();

        $this->assertStringContainsString('JUAN DELA CRUZ', $html);
        $this->assertStringContainsString('June 25, 2026, 9:00 AM', $html);
        $this->assertStringContainsString('Municipal Gymnasium', $html);
        $this->assertStringContainsString('Citizen ID distribution', $html);
    }
}
