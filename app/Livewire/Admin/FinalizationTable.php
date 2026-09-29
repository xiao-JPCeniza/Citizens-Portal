<?php

namespace App\Livewire\Admin;

use App\Models\Applicant;
use App\Models\Barangay;
use App\Support\AdminTable;
use App\Support\ManoloFortich;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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
