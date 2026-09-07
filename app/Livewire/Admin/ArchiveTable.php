<?php

namespace App\Livewire\Admin;

use App\Enums\ApplicantStatus;
use App\Models\Applicant;
use App\Support\AdminTable;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Archive')]
class ArchiveTable extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'type', history: true)]
    public string $type = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        if (! in_array($this->type, ['all', 'rejected', 'card_delivered'], true)) {
            $this->type = 'all';
        }

        $this->resetPage();
    }

    public function render()
    {
        $query = Applicant::query()
            ->archived()
            ->with('verifier')
            ->search($this->search);

        if ($this->type === 'card_delivered') {
            $query->where('status', ApplicantStatus::Approved)
                ->where('rejection_reason', Applicant::CARD_DELIVERED_REASON);
        } elseif ($this->type === 'rejected') {
            $query->where('status', ApplicantStatus::Rejected);
        }

        return view('livewire.admin.archive-table', [
            'applicants' => $query->paginate(AdminTable::PER_PAGE),
        ]);
    }
}
