<?php

namespace App\Livewire\Admin;

use App\Services\ApplicantStatisticsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(ApplicantStatisticsService $statisticsService)
    {
        return view('livewire.admin.dashboard', [
            'stats' => $statisticsService->getSummary(),
        ]);
    }
}
