<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Safety net for environments whose database was restored from a dump
     * that listed these migrations as run while missing the actual tables.
     */
    public function up(): void
    {
        foreach ($this->statements() as $sql) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        // Intentionally left empty: dropping live cache/session/job tables
        // during rollback would destroy data for 
    }

    private function statements(): array
    {
        return [
            'CREATE TABLE IF NOT EXISTS `cache` (
                `key` varchar(255) NOT NULL PRIMARY KEY,
                `value` mediumtext NOT NULL,
                `expiration` int NOT NULL,
                KEY `cache_expiration_index` (`expiration`)
            )',
            'CREATE TABLE IF NOT EXISTS `cache_locks` (
                `key` varchar(255) NOT NULL PRIMARY KEY,
                `owner` varchar(255) NOT NULL,
                `expiration` int NOT NULL
            )',
            'CREATE TABLE IF NOT EXISTS `jobs` (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                queue VARCHAR(255) NOT NULL,
                payload LONGTEXT NOT NULL,
                attempts TINYINT UNSIGNED NOT NULL,
                reserved_at INT UNSIGNED NULL,
                available_at INT UNSIGNED NOT NULL,
                created_at INT UNSIGNED NOT NULL,
                KEY jobs_queue_index (queue)
            )',
        ];
    }
};
