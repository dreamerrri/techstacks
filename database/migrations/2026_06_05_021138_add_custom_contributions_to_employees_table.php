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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('custom_sss_contribution', 10, 2)->nullable()->after('pagibig_cap');
            $table->decimal('custom_philhealth_contribution', 10, 2)->nullable()->after('custom_sss_contribution');
            $table->decimal('custom_pagibig_contribution', 10, 2)->nullable()->after('custom_philhealth_contribution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['custom_sss_contribution', 'custom_philhealth_contribution', 'custom_pagibig_contribution']);
        });
    }
};
