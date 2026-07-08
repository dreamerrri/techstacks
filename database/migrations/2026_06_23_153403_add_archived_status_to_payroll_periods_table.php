<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Use Schema::getColumnListing() for database-agnostic column type checking
        if (Schema::hasTable('payroll_periods')) {
            $columnType = null;
            
            // Get column type using database-agnostic method
            if (DB::getDriverName() === 'mysql') {
                $column = DB::select("SHOW COLUMNS FROM payroll_periods WHERE Field = 'status'")[0]->Type ?? '';
                $columnType = $column;
            } else {
                // For SQLite and other databases, skip the check and just run the alter
                // SQLite doesn't support ENUM, so this migration is MySQL-specific
                return;
            }

            if ($columnType && !str_contains($columnType, 'archived')) {
                DB::statement("ALTER TABLE payroll_periods MODIFY COLUMN status ENUM('draft', 'finalized', 'archived') NOT NULL DEFAULT 'draft'");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payroll_periods MODIFY COLUMN status ENUM('draft', 'finalized') NOT NULL DEFAULT 'draft'");
        }
    }
};