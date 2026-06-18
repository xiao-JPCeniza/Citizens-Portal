<?php

namespace App\Livewire\Admin;

use App\Models\Applicant;
use App\Models\Barangay;
use App\Services\DistributionEmailService;
use App\Support\AdminTable;
use App\Support\ManoloFortich;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Finalized Applications')]
class FinalizationTable extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'barangay', history: true)]
    public string $barangay = '';

    #[Url(as: 'from', history: true)]
    public string $date_from = '';

    #[Url(as: 'to', history: true)]
    public string $date_to = '';

    /** @var array<int, string> */
    public array $selectedApplicants = [];

    public bool $showEmailModal = false;

    public string $when = '';

    public string $where = '';

    public string $what = '';

    public $poster_photo = null;

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

    public function toggleSelectAllOnPage(): void
    {
        $pageIds = $this->currentPageApplicantIds();

        if ($this->allOnPageSelected()) {
            $this->selectedApplicants = array_values(array_diff($this->selectedApplicants, $pageIds));
        } else {
            $this->selectedApplicants = array_values(array_unique(array_merge($this->selectedApplicants, $pageIds)));
        }
    }

    public function openEmailModal(): void
    {
        if ($this->selectedApplicants === []) {
            return;
        }

        $this->resetErrorBag();
        $this->showEmailModal = true;
    }

    public function closeEmailModal(): void
    {
        $this->showEmailModal = false;
        $this->reset(['when', 'where', 'what', 'poster_photo']);
        $this->resetValidation();
    }

    public function sendDistributionEmail(DistributionEmailService $distributionEmailService): void
    {
        $this->validate([
            'selectedApplicants' => 'required|array|min:1',
            'when' => 'required|string|max:500',
            'where' => 'required|string|max:500',
            'what' => 'required|string|max:2000',
            'poster_photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'selectedApplicants.min' => 'Select at least one applicant.',
            'poster_photo.mimes' => 'Poster photo must be a JPG or PNG file.',
        ]);

        $applicants = Applicant::query()
            ->finalized()
            ->whereIn('id', $this->normalizedSelectedApplicants())
            ->get();

        if ($applicants->isEmpty()) {
            $this->addError('selectedApplicants', 'No valid finalized applicants were found for the current selection.');

            return;
        }

        $posterPath = $this->poster_photo->getRealPath();

        $sent = $distributionEmailService->send(
            $applicants,
            Auth::guard('admin')->user(),
            $this->when,
            $this->where,
            $this->what,
            $posterPath,
        );

        $this->reset(['selectedApplicants', 'when', 'where', 'what', 'poster_photo', 'showEmailModal']);

        session()->flash('success', "Distribution email sent to {$sent} applicant(s).");
    }

    public function allOnPageSelected(): bool
    {
        $pageIds = $this->currentPageApplicantIds();

        if ($pageIds === []) {
            return false;
        }

        return empty(array_diff($pageIds, $this->selectedApplicants));
    }

    /**
     * @return array<int, int>
     */
    protected function normalizedSelectedApplicants(): array
    {
        return array_map(fn ($id) => (int) $id, $this->selectedApplicants);
    }

    /**
     * @return array<int, string>
     */
    protected function currentPageApplicantIds(): array
    {
        return Applicant::query()
            ->finalized()
            ->search($this->search)
            ->inBarangay($this->barangay)
            ->approvedBetween($this->date_from, $this->date_to)
            ->forPage($this->getPage(), AdminTable::PER_PAGE)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function render()
    {
        return view('livewire.admin.finalization-table', [
            'applicants' => Applicant::query()
                ->finalized()
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
