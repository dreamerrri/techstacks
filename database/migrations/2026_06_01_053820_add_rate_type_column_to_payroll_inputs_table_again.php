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
            if (!Schema::hasColumn('payroll_inputs', 'rate_type')) {
                $table->string('rate_type')->default('daily')->after('daily_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_inputs', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_inputs', 'rate_type')) {
                $table->dropColumn('rate_type');
            }
        });
    }
};
