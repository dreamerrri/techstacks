<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations - Export database to backup file
     */
    public function up(): void
    {
        $database = env('DB_DATABASE', 'techstacks');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }
        
        $timestamp = date('Y_m_d_His');
        $backupFile = "{$backupDir}/techstacks_backup_{$timestamp}.sql";
        
        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h %s -P %s -u %s %s %s > %s',
            $host,
            $port,
            $username,
            $password ? '-p' . escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );
        
        // Execute backup command
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            // Backup created successfully
        } else {
            // Failed to create database backup
        }
    }

    /**
     * Reverse the migrations - Import database from backup file
     */
    public function down(): void
    {
        $database = env('DB_DATABASE', 'techstacks');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        
        $backupDir = storage_path('app/backups');
        
        // Find the most recent backup file
        $files = glob("{$backupDir}/techstacks_backup_*.sql");
        
        if (empty($files)) {
            // No backup files found
            return;
        }
        
        // Sort by modification time, get the most recent
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $backupFile = $files[0];
        
        // Build mysql import command
        $command = sprintf(
            'mysql -h %s -P %s -u %s %s %s < %s',
            $host,
            $port,
            $username,
            $password ? '-p' . escapeshellarg($password) : '',
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );
        
        // Execute import command
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            // Database restored successfully
        } else {
            // Failed to restore database
        }
    }
};
