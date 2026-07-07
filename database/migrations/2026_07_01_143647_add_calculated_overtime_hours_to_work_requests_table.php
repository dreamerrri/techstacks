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
        Schema::table('work_requests', function (Blueprint $table) {
            $table->decimal('calculated_overtime_hours', 5, 2)->nullable()->after('estimated_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_requests', function (Blueprint $table) {
            $table->dropColumn('calculated_overtime_hours');
        });
    }
};
