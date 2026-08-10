<?php

use App\Services\ApplicantPhotoNormalizationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('application_id', 6)->nullable()->unique()->after('id');
        });

        app(ApplicantPhotoNormalizationService::class)->normalizeExistingApplicants();

        Schema::table('applicants', function (Blueprint $table) {
            $table->string('application_id', 6)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropUnique(['application_id']);
            $table->dropColumn('application_id');
        });
    }
};
