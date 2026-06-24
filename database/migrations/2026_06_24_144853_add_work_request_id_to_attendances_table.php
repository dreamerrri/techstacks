<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('work_request_id')->nullable()->after('employee_id')->constrained('work_requests')->onDelete('set null');
            $table->index('work_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['work_request_id']);
            $table->dropIndex(['work_request_id']);
            $table->dropColumn('work_request_id');
        });
    }
};
