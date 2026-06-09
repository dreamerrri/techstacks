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
            $table->string('deductions_remarks', 255)->nullable()->after('deductions');
            $table->string('reimbursements_remarks', 255)->nullable()->after('reimbursements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_inputs', function (Blueprint $table) {
            $table->dropColumn(['deductions_remarks', 'reimbursements_remarks']);
        });
    }
};
