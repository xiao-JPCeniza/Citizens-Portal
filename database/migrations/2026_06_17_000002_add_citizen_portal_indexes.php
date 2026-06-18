<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index('barangay');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('admin_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['barangay']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['admin_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
