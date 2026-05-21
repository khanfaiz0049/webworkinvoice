<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

/**
 * Artisan command: php artisan backup:run
 *
 * Creates a timestamped database backup stored in storage/app/backups/.
 * Supports optional gzip compression.
 *
 * Usage:
 *   php artisan backup:run
 *   php artisan backup:run --gzip
 */
class BackupRun extends Command
{
    protected $signature   = 'backup:run {--gzip : Compress the backup with gzip}';
    protected $description = 'Create a timestamped database backup';

    public function handle(BackupService $backupService): int
    {
        $gzip = (bool) $this->option('gzip');

        $this->info('🔄  Starting database backup...');

        // Show health summary first
        $health = $backupService->healthCheck();
        $this->table(
            ['Check', 'Status'],
            [
                ['DB Connection',    $health['mysql_connection'] ? '✓ OK' : '✗ FAIL'],
                ['mysqldump',        $health['mysqldump_found']  ? '✓ Found: ' . $health['mysqldump_path'] : '✗ NOT FOUND'],
                ['Backup Folder',    $health['backup_folder_ok'] ? '✓ Writable' : '✗ NOT WRITABLE'],
            ]
        );

        if (!$health['mysql_connection'] || !$health['mysqldump_found'] || !$health['backup_folder_ok']) {
            $this->error('Pre-flight checks failed. Cannot proceed with backup.');
            return self::FAILURE;
        }

        $result = $backupService->createBackup(gzip: $gzip);

        if ($result['success']) {
            $this->info('✅  ' . $result['message']);
            return self::SUCCESS;
        }

        $this->error('✗  Backup failed: ' . $result['message']);
        return self::FAILURE;
    }
}
