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
            $table->dropColumn(['sss_rate', 'sss_cap', 'philhealth_rate', 'philhealth_cap', 'pagibig_cap']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('sss_rate', 5, 4)->default(0.0450)->after('tin_number');
            $table->decimal('sss_cap', 10, 2)->default(900.00)->after('sss_rate');
            $table->decimal('philhealth_rate', 5, 4)->default(0.0225)->after('sss_cap');
            $table->decimal('philhealth_cap', 10, 2)->default(1500.00)->after('philhealth_rate');
            $table->decimal('pagibig_cap', 10, 2)->default(100.00)->after('pagibig_rate');
        });
    }
};
