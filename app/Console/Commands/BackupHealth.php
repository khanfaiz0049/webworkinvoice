<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

/**
 * Artisan command: php artisan backup:health
 *
 * Displays a full system diagnostic report for the backup subsystem.
 *
 * Usage:
 *   php artisan backup:health
 *   php artisan backup:health --json
 */
class BackupHealth extends Command
{
    protected $signature   = 'backup:health {--json : Output as JSON}';
    protected $description = 'Display backup system diagnostics';

    public function handle(BackupService $backupService): int
    {
        $health = $backupService->healthCheck();

        if ($this->option('json')) {
            $this->line(json_encode($health, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║      BACKUP SYSTEM DIAGNOSTICS           ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('');

        $this->table(
            ['Check', 'Result'],
            [
                ['Operating System',     $health['os']],
                ['Database Connection',  $health['mysql_connection']  ? '✓ Connected' : '✗ FAILED'],
                ['mysqldump Binary',     $health['mysqldump_found']   ? '✓ ' . $health['mysqldump_path'] : '✗ NOT FOUND'],
                ['mysql Binary',         $health['mysql_found']       ? '✓ ' . $health['mysql_path']    : '✗ NOT FOUND'],
                ['Backup Folder',        $health['backup_folder_ok']  ? '✓ Writable' : '✗ NOT WRITABLE'],
                ['Backup Folder Path',   $health['backup_folder']],
                ['Database Size',        $health['database_size_mb'] !== null ? $health['database_size_mb'] . ' MB' : 'N/A'],
                ['Total Backup Files',   $health['backup_count']],
                ['Latest Backup',        $health['latest_backup'] ?? 'None yet'],
                ['Latest Backup File',   $health['latest_backup_file'] ?? 'None yet'],
            ]
        );

        $allOk = $health['mysql_connection'] && $health['mysqldump_found'] && $health['mysql_found'] && $health['backup_folder_ok'];

        $this->info('');
        if ($allOk) {
            $this->info('✅  All systems operational. Ready to backup/restore.');
        } else {
            $this->error('⚠   One or more checks failed. Review the table above.');
        }
        $this->info('');

        return $allOk ? self::SUCCESS : self::FAILURE;
    }
}
