<?php

namespace App\Support;

use App\Enums\RejectionReason;

class ApplicantCorrectionScope
{
    public function __construct(
        public readonly ?RejectionReason $reason,
    ) {}

    public static function fromRejectionReason(?string $storedReason): self
    {
        return new self(RejectionReason::fromStored($storedReason));
    }

    public function requiresPassportPhoto(): bool
    {
        return $this->reason?->requiresPassportCorrection() ?? false;
    }

    public function requiresGcashScreenshot(): bool
    {
        return $this->reason?->requiresGcashCorrection() ?? false;
    }

    public function allowsInformationCorrection(): bool
    {
        return $this->reason?->allowsInformationCorrection() ?? false;
    }

    public function requiresDocuments(): bool
    {
        return $this->requiresPassportPhoto() || $this->requiresGcashScreenshot();
    }

    public function instruction(): string
    {
        return match ($this->reason) {
            RejectionReason::InvalidPassportPhoto => 'Upload a new passport photo that meets the requirements, then resubmit.',
            RejectionReason::InvalidGcashScreenshot => 'Upload a new GCash screenshot that matches your application, then resubmit.',
            RejectionReason::UnreadableDocuments => 'Upload clear replacements for the required documents, then resubmit.',
            RejectionReason::IncompleteInformation => 'Update the incomplete application details, then resubmit.',
            default => 'Review the rejection reason and submit the required corrections.',
        };
    }
}
