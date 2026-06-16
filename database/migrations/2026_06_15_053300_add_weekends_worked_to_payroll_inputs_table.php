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
        Schema::table('payroll_inputs', function (Blueprint $table) {
            $table->decimal('weekends_worked', 8, 2)->default(0)->after('days_worked');
            $table->dropColumn('regular_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_inputs', function (Blueprint $table) {
            $table->decimal('regular_hours', 8, 2)->default(0)->after('days_worked');
            $table->dropColumn('weekends_worked');
        });
    }
};
