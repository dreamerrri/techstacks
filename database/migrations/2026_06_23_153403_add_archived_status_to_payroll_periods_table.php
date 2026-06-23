<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $column = DB::select("SHOW COLUMNS FROM payroll_periods WHERE Field = 'status'")[0]->Type ?? '';

        if (!str_contains($column, 'archived')) {
            DB::statement("ALTER TABLE payroll_periods MODIFY COLUMN status ENUM('draft', 'finalized', 'archived') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payroll_periods MODIFY COLUMN status ENUM('draft', 'finalized') NOT NULL DEFAULT 'draft'");
    }
};