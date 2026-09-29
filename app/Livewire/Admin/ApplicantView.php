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

    public bool $showReturnForm = false;

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

        session()->flash('success', "Application for {$this->applicant->full_name} has been accepted and moved to the Verification Dashboard.");

        $this->redirect(route('admin.applications.index'), navigate: true);
    }

    public function verify(ApplicantVerificationService $verificationService): void
    {
        try {
            $verificationService->verify($this->applicant, auth('admin')->user());
        } catch (ValidationException $exception) {
            $this->setValidationErrors($exception);

            return;
        }

        session()->flash('success', "Application for {$this->applicant->full_name} has been approved and moved to Approved Applications.");

        $this->redirect(route('admin.finalized.index'), navigate: true);
    }

    public function showReject(): void
    {
        $this->resetDecisionForm();
        $this->showRejectForm = true;
    }

    public function showReturn(): void
    {
        $this->resetDecisionForm();
        $this->showReturnForm = true;
    }

    public function cancelReject(): void
    {
        $this->resetDecisionForm();
    }

    public function returnApplication(ApplicantVerificationService $verificationService): void
    {
        $this->validate([
            'rejection_reason' => ['required', 'string', Rule::in($this->reasonValues(self::returnReasons()))],
            'remarks' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Please select what needs to be corrected.',
            'remarks.required' => 'Please provide remarks for the applicant.',
        ]);

        try {
            $verificationService->reject(
                $this->applicant,
                auth('admin')->user(),
                RejectionReason::from($this->rejection_reason),
                $this->remarks,
            );
        } catch (ValidationException $exception) {
            $this->setValidationErrors($exception);

            return;
        }

        session()->flash(
            'success',
            "Application for {$this->applicant->full_name} has been returned to the applicant for correction and moved back to New Applicants.",
        );

        $this->redirect(route('admin.finalized.index'), navigate: true);
    }

    public function reject(ApplicantVerificationService $verificationService): void
    {
        $fromVerification = $this->applicant->isForVerification();
        $allowedReasons = $fromVerification ? self::finalRejectionReasons() : RejectionReason::cases();

        $this->validate([
            'rejection_reason' => ['required', 'string', Rule::in($this->reasonValues($allowedReasons))],
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

        if ($reason->allowsEditLink()) {
            session()->flash(
                'success',
                "Application for {$this->applicant->full_name} remains pending. The applicant was asked to submit the required documents.",
            );
        } else {
            session()->flash(
                'success',
                "Application for {$this->applicant->full_name} has been rejected and moved to Archive.",
            );
        }

        $this->redirect(
            route($fromVerification ? 'admin.finalized.index' : 'admin.applications.index'),
            navigate: true,
        );
    }

    /**
     * @return list<RejectionReason>
     */
    public static function returnReasons(): array
    {
        return array_values(array_filter(
            RejectionReason::cases(),
            fn (RejectionReason $reason) => $reason->allowsEditLink(),
        ));
    }

    /**
     * @return list<RejectionReason>
     */
    public static function finalRejectionReasons(): array
    {
        return array_values(array_filter(
            RejectionReason::cases(),
            fn (RejectionReason $reason) => ! $reason->allowsEditLink(),
        ));
    }

    /**
     * @param  array<int, RejectionReason>  $reasons
     * @return list<string>
     */
    protected function reasonValues(array $reasons): array
    {
        return array_values(array_map(fn (RejectionReason $reason) => $reason->value, $reasons));
    }

    protected function resetDecisionForm(): void
    {
        $this->showRejectForm = false;
        $this->showReturnForm = false;
        $this->rejection_reason = '';
        $this->remarks = '';
        $this->resetErrorBag();
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
            'returnReasons' => self::returnReasons(),
            'finalRejectionReasons' => self::finalRejectionReasons(),
        ]);
    }
}
