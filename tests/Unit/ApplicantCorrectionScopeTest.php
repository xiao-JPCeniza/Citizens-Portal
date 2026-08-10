<?php

namespace Tests\Unit;

use App\Enums\RejectionReason;
use App\Support\ApplicantCorrectionScope;
use PHPUnit\Framework\TestCase;

class ApplicantCorrectionScopeTest extends TestCase
{
    public function test_it_parses_stored_rejection_reasons_with_remarks(): void
    {
        $this->assertSame(
            RejectionReason::InvalidGcashScreenshot,
            RejectionReason::fromStored('Invalid GCash Screenshot: Blurry image.'),
        );

        $this->assertSame(
            RejectionReason::InvalidPassportPhoto,
            RejectionReason::fromStored('Invalid Passport Photo'),
        );
    }

    public function test_passport_rejection_requires_only_passport(): void
    {
        $scope = ApplicantCorrectionScope::fromRejectionReason('Invalid Passport Photo: Bad background');

        $this->assertTrue($scope->requiresPassportPhoto());
        $this->assertFalse($scope->requiresGcashScreenshot());
        $this->assertFalse($scope->allowsInformationCorrection());
    }

    public function test_gcash_rejection_requires_only_gcash(): void
    {
        $scope = ApplicantCorrectionScope::fromRejectionReason('Invalid GCash Screenshot');

        $this->assertFalse($scope->requiresPassportPhoto());
        $this->assertTrue($scope->requiresGcashScreenshot());
        $this->assertFalse($scope->allowsInformationCorrection());
    }

    public function test_unreadable_documents_requires_both_uploads(): void
    {
        $scope = ApplicantCorrectionScope::fromRejectionReason('Unreadable Documents');

        $this->assertTrue($scope->requiresPassportPhoto());
        $this->assertTrue($scope->requiresGcashScreenshot());
        $this->assertFalse($scope->allowsInformationCorrection());
    }

    public function test_incomplete_information_allows_info_only(): void
    {
        $scope = ApplicantCorrectionScope::fromRejectionReason('Incomplete Information: Missing address details');

        $this->assertFalse($scope->requiresPassportPhoto());
        $this->assertFalse($scope->requiresGcashScreenshot());
        $this->assertTrue($scope->allowsInformationCorrection());
    }
}
