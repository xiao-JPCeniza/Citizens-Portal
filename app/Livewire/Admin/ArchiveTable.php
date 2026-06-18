<?php

namespace App\Livewire\Admin;

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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.archive-table', [
            'applicants' => Applicant::query()
                ->archived()
                ->with('verifier')
                ->search($this->search)
                ->paginate(AdminTable::PER_PAGE),
        ]);
    }
}
