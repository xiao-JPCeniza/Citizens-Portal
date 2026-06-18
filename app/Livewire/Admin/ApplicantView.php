<?php

namespace App\Livewire\Admin;

use App\Enums\RejectionReason;
use App\Models\Applicant;
use App\Services\AdminActivityLogService;
use App\Services\ApplicantVerificationService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Review Application')]
class ApplicantView extends Component
{
    public Applicant $applicant;

    public bool $showRejectForm = false;

    public string $rejection_reason = '';

    public string $remarks = '';

    public function mount(Applicant $applicant, AdminActivityLogService $activityLogService): void
    {
        $this->applicant = $applicant->load('verifier');

        $activityLogService->log(
            auth('admin')->user(),
            'Application Viewed',
            "Viewed application for {$applicant->full_name}.",
        );
    }

    public function approve(ApplicantVerificationService $verificationService): void
    {
        try {
            $verificationService->approve($this->applicant, auth('admin')->user());
        } catch (ValidationException $exception) {
            $this->setValidationErrors($exception);

            return;
        }

        session()->flash('success', "Application for {$this->applicant->full_name} has been approved and moved to Finalization.");

        $this->redirect(route('admin.finalized.index'), navigate: true);
    }

    public function showReject(): void
    {
        $this->showRejectForm = true;
        $this->resetErrorBag();
    }

    public function cancelReject(): void
    {
        $this->showRejectForm = false;
        $this->rejection_reason = '';
        $this->remarks = '';
        $this->resetErrorBag();
    }

    public function reject(ApplicantVerificationService $verificationService): void
    {
        $reasonValues = array_column(RejectionReason::cases(), 'value');

        $this->validate([
            'rejection_reason' => ['required', 'string', Rule::in($reasonValues)],
            'remarks' => [
                Rule::requiredIf(fn () => $this->rejection_reason === RejectionReason::Other->value),
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'rejection_reason.required' => 'Please select a rejection reason.',
            'remarks.required' => 'Please provide remarks when selecting Other.',
        ]);

        $reason = RejectionReason::from($this->rejection_reason);

        try {
            $verificationService->reject(
                $this->applicant,
                auth('admin')->user(),
                $reason,
                $this->remarks,
            );
        } catch (ValidationException $exception) {
            $this->setValidationErrors($exception);

            return;
        }

        session()->flash('success', "Application for {$this->applicant->full_name} has been rejected and moved to Archive.");

        $this->redirect(route('admin.applications.index'), navigate: true);
    }

    protected function setValidationErrors(ValidationException $exception): void
    {
        foreach ($exception->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $this->addError($field, $message);
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.applicant-view', [
            'rejectionReasons' => RejectionReason::cases(),
        ]);
    }
}
