<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('edit_token_hash', 64)->nullable()->after('verified_at');
            $table->timestamp('edit_token_expires_at')->nullable()->after('edit_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['edit_token_hash', 'edit_token_expires_at']);
        });
    }
};
