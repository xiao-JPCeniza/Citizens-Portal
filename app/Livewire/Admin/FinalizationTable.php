<?php

namespace App\Livewire\Admin;

use App\Models\Applicant;
use App\Models\Barangay;
use App\Services\ApplicantVerificationService;
use App\Support\AdminTable;
use App\Support\ManoloFortich;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

#[Layout('layouts.admin')]
#[Title('Verification Dashboard')]
class FinalizationTable extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'barangay', history: true)]
    public string $barangay = '';

    #[Url(as: 'from', history: true)]
    public string $date_from = '';

    #[Url(as: 'to', history: true)]
    public string $date_to = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBarangay(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'barangay', 'date_from', 'date_to']);
        $this->resetPage();
    }

    /**
     * @param  array<int, int|string>  $applicantIds
     */
    public function approvePage(array $applicantIds, ApplicantVerificationService $verificationService): void
    {
        $ids = collect($applicantIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->take(AdminTable::PER_PAGE)
            ->values()
            ->all();

        $applicants = Applicant::query()
            ->forVerification()
            ->whereIn('id', $ids)
            ->get();

        if ($applicants->isEmpty()) {
            session()->flash('error', 'No applications awaiting verification were found on this page.');

            return;
        }

        $admin = Auth::guard('admin')->user();

        foreach ($applicants as $applicant) {
            try {
                $verificationService->verify($applicant, $admin);
            } catch (ValidationException) {
                continue;
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $approved = Applicant::query()->verified()->whereIn('id', $applicants->modelKeys())->count();
        $skipped = $applicants->count() - $approved;

        $this->resetPage();

        session()->flash(
            'success',
            "Approved {$approved} application(s) and moved them to Approved Applications."
                .($skipped > 0 ? " {$skipped} could not be approved." : ''),
        );
    }

    public function render()
    {
        return view('livewire.admin.finalization-table', [
            'applicants' => Applicant::query()
                ->forVerification()
                ->with('verifier')
                ->search($this->search)
                ->inBarangay($this->barangay)
                ->approvedBetween($this->date_from, $this->date_to)
                ->paginate(AdminTable::PER_PAGE),
            'barangays' => Barangay::query()
                ->active()
                ->forMunicipality(ManoloFortich::PROVINCE)
                ->orderBy('name')
                ->pluck('name'),
        ]);
    }
}
