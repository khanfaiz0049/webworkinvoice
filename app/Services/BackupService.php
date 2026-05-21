<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

/**
 * BackupService
 *
 * Handles all database backup and restore operations.
 * - Auto-detects Windows/XAMPP and Linux environments
 * - Finds mysqldump/mysql binaries automatically (with .env overrides)
 * - Creates timestamped, compressed backups
 * - Validates and safely restores SQL files
 * - Cleans up backups older than 30 days
 * - Logs all operations
 */
class BackupService
{
    /** Disk used for storing backups (local by default) */
    protected string $disk = 'local';

    /** Relative path inside storage/app/ */
    protected string $backupFolder = 'backups';

    /** Number of days to keep old backups */
    protected int $retentionDays = 30;

    /** Maximum allowed size for an uploaded SQL file (50 MB) */
    protected int $maxUploadBytes = 52_428_800;

    // ─────────────────────────────────────────────────────────────────────────
    // PATH DETECTION
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Detect whether the app is running on Windows or Linux.
     */
    public function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Return candidate paths for mysqldump based on OS.
     */
    protected function candidateMysqldumpPaths(): array
    {
        if ($this->isWindows()) {
            return [
                'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                'C:\\xampp7\\mysql\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
                'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
                'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            ];
        }

        return [
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump',   // macOS (Homebrew)
        ];
    }

    /**
     * Return candidate paths for mysql CLI based on OS.
     */
    protected function candidateMysqlPaths(): array
    {
        if ($this->isWindows()) {
            return [
                'C:\\xampp\\mysql\\bin\\mysql.exe',
                'C:\\xampp7\\mysql\\bin\\mysql.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysql.exe',
                'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysql.exe',
                'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysql.exe',
            ];
        }

        return [
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            '/usr/local/mysql/bin/mysql',
            '/opt/homebrew/bin/mysql',
        ];
    }

    /**
     * Resolve the path to mysqldump.
     * Priority: .env value → auto-detected paths → null
     */
    public function resolveMysqldumpPath(): ?string
    {
        // 1. Explicit .env override
        $envPath = env('MYSQLDUMP_PATH');
        if ($envPath && file_exists($envPath)) {
            Log::channel('backup')->info("BackupService: mysqldump resolved from .env: {$envPath}");
            return $envPath;
        }

        // 2. Auto-detect
        foreach ($this->candidateMysqldumpPaths() as $path) {
            if (file_exists($path)) {
                Log::channel('backup')->info("BackupService: mysqldump auto-detected: {$path}");
                return $path;
            }
        }

        // 3. Try PATH (Linux servers / cPanel)
        $which = $this->isWindows() ? 'where mysqldump 2>nul' : 'which mysqldump 2>/dev/null';
        $result = trim((string) shell_exec($which));
        if ($result && file_exists($result)) {
            Log::channel('backup')->info("BackupService: mysqldump found via PATH: {$result}");
            return $result;
        }

        Log::channel('backup')->error('BackupService: mysqldump not found.');
        return null;
    }

    /**
     * Resolve the path to the mysql CLI.
     * Priority: .env value → auto-detected paths → null
     */
    public function resolveMysqlPath(): ?string
    {
        $envPath = env('MYSQL_PATH');
        if ($envPath && file_exists($envPath)) {
            Log::channel('backup')->info("BackupService: mysql resolved from .env: {$envPath}");
            return $envPath;
        }

        foreach ($this->candidateMysqlPaths() as $path) {
            if (file_exists($path)) {
                Log::channel('backup')->info("BackupService: mysql auto-detected: {$path}");
                return $path;
            }
        }

        $which = $this->isWindows() ? 'where mysql 2>nul' : 'which mysql 2>/dev/null';
        $result = trim((string) shell_exec($which));
        if ($result && file_exists($result)) {
            Log::channel('backup')->info("BackupService: mysql found via PATH: {$result}");
            return $result;
        }

        Log::channel('backup')->error('BackupService: mysql not found.');
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VERIFICATION / HEALTH CHECK
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Run a full diagnostic and return a structured status array.
     */
    public function healthCheck(): array
    {
        $mysqldump  = $this->resolveMysqldumpPath();
        $mysql      = $this->resolveMysqlPath();
        $folderPath = Storage::disk($this->disk)->path($this->backupFolder);
        $folderOk   = is_dir($folderPath) ? is_writable($folderPath) : $this->ensureBackupFolder();
        $dbOk       = $this->testDbConnection();
        $dbSize     = $dbOk ? $this->getDatabaseSize() : null;
        $latestFile = $this->getLatestBackupFile();
        $latestDate = $latestFile ? Carbon::createFromTimestamp(filemtime(Storage::disk($this->disk)->path($latestFile))) : null;
        $backupCount = count($this->listBackupFiles());

        return [
            'os'                 => $this->isWindows() ? 'Windows' : PHP_OS,
            'mysql_connection'   => $dbOk,
            'mysqldump_path'     => $mysqldump,
            'mysqldump_found'    => (bool) $mysqldump,
            'mysql_path'         => $mysql,
            'mysql_found'        => (bool) $mysql,
            'backup_folder'      => $folderPath,
            'backup_folder_ok'   => $folderOk,
            'database_size_mb'   => $dbSize,
            'latest_backup'      => $latestDate?->toDateTimeString(),
            'latest_backup_file' => $latestFile ? basename($latestFile) : null,
            'backup_count'       => $backupCount,
        ];
    }

    /**
     * Test that the database connection works.
     */
    public function testDbConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable $e) {
            Log::channel('backup')->error('BackupService: DB connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Return the database size in megabytes, rounded to 2 dp.
     */
    public function getDatabaseSize(): ?float
    {
        try {
            $db = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$db]);

            return isset($result[0]) ? (float) $result[0]->size_mb : 0.0;
        } catch (\Throwable $e) {
            Log::channel('backup')->error('BackupService: getDatabaseSize failed: ' . $e->getMessage());
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BACKUP
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a database backup.
     *
     * @param  bool  $gzip  Whether to gzip-compress the output file.
     * @return array{success: bool, message: string, filename: ?string, path: ?string}
     */
    public function createBackup(bool $gzip = false): array
    {
        Log::channel('backup')->info('BackupService: Starting backup. gzip=' . ($gzip ? 'true' : 'false'));

        // ── Pre-flight checks ──────────────────────────────────────────────
        $check = $this->preflightCheck(needMysql: false);
        if (!$check['ok']) {
            return ['success' => false, 'message' => $check['message'], 'filename' => null, 'path' => null];
        }

        $this->ensureBackupFolder();
        $this->cleanupOldBackups();

        // ── Build output filename ──────────────────────────────────────────
        $db        = config('database.connections.mysql.database');
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $ext       = $gzip ? 'sql.gz' : 'sql';
        $filename  = "backup_{$db}_{$timestamp}.{$ext}";
        $filePath  = Storage::disk($this->disk)->path("{$this->backupFolder}/{$filename}");

        // ── Build the mysqldump command ────────────────────────────────────
        $mysqldump = $check['mysqldump'];
        $host      = config('database.connections.mysql.host', '127.0.0.1');
        $port      = config('database.connections.mysql.port', '3306');
        $user      = config('database.connections.mysql.username', 'root');
        $password  = config('database.connections.mysql.password', '');

        // Use escapeshellarg to prevent command injection
        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s %s --single-transaction --routines --triggers --events %s',
            escapeshellarg($mysqldump),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $password !== '' ? '--password=' . escapeshellarg($password) : '--password=',
            escapeshellarg($db)
        );

        if ($gzip && !$this->isWindows()) {
            // On Linux/macOS: pipe directly through gzip
            $cmd .= ' | gzip > ' . escapeshellarg($filePath);
        } elseif ($gzip && $this->isWindows()) {
            // On Windows: dump to temp file, then use PHP's gzip
            $tmpFile = $filePath . '.tmp';
            $cmd .= ' > ' . escapeshellarg($tmpFile);
        } else {
            $cmd .= ' > ' . escapeshellarg($filePath);
        }

        // ── Execute ───────────────────────────────────────────────────────
        $output     = [];
        $returnCode = 0;

        if ($this->isWindows()) {
            // Windows: exec() with error redirect
            exec("{$cmd} 2>&1", $output, $returnCode);
        } else {
            // Linux: run and capture stderr
            $process = "{$cmd} 2>&1";
            exec($process, $output, $returnCode);
        }

        // ── Handle Windows gzip fallback ──────────────────────────────────
        if ($gzip && $this->isWindows() && $returnCode === 0 && isset($tmpFile) && file_exists($tmpFile)) {
            $this->gzipFile($tmpFile, $filePath);
            @unlink($tmpFile);
        }

        // ── Validate result ────────────────────────────────────────────────
        $actualFile = ($gzip && $this->isWindows()) ? $filePath : $filePath;

        if ($returnCode !== 0 || !file_exists($actualFile) || filesize($actualFile) < 100) {
            $errMsg = implode("\n", $output);
            Log::channel('backup')->error("BackupService: backup FAILED. Return code={$returnCode}. Output={$errMsg}");
            return [
                'success'  => false,
                'message'  => 'Backup failed: ' . ($errMsg ?: 'Unknown error. Check backup log.'),
                'filename' => null,
                'path'     => null,
            ];
        }

        $sizeMb = round(filesize($actualFile) / 1024 / 1024, 2);
        Log::channel('backup')->info("BackupService: backup SUCCESS. File={$filename}, Size={$sizeMb}MB");

        return [
            'success'  => true,
            'message'  => "Backup created successfully: {$filename} ({$sizeMb} MB)",
            'filename' => $filename,
            'path'     => $actualFile,
        ];
    }

    /**
     * PHP-based gzip compression for Windows environments without native gzip.
     */
    protected function gzipFile(string $source, string $destination): bool
    {
        $in  = fopen($source, 'rb');
        $out = gzopen($destination, 'wb9');

        if (!$in || !$out) {
            return false;
        }

        while (!feof($in)) {
            gzwrite($out, fread($in, 65536));
        }

        fclose($in);
        gzclose($out);

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RESTORE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Restore the database from an uploaded SQL file.
     *
     * Creates an emergency backup first, validates the file, then imports.
     *
     * @param  string  $uploadedFilePath  Absolute path to the uploaded .sql file.
     * @return array{success: bool, message: string, emergency_backup: ?string, log: string[]}
     */
    public function restoreBackup(string $uploadedFilePath): array
    {
        $log = [];
        Log::channel('backup')->info("BackupService: Starting restore from: {$uploadedFilePath}");

        // ── Validate the uploaded file ─────────────────────────────────────
        $validate = $this->validateSqlFile($uploadedFilePath);
        if (!$validate['ok']) {
            return [
                'success'          => false,
                'message'          => $validate['message'],
                'emergency_backup' => null,
                'log'              => [$validate['message']],
            ];
        }
        $log[] = '✓ File validated successfully.';

        // ── Pre-flight checks ──────────────────────────────────────────────
        $check = $this->preflightCheck(needMysql: true);
        if (!$check['ok']) {
            return [
                'success'          => false,
                'message'          => $check['message'],
                'emergency_backup' => null,
                'log'              => [$check['message']],
            ];
        }
        $log[] = '✓ Environment checks passed.';

        // ── Create emergency backup ────────────────────────────────────────
        $log[] = '⏳ Creating emergency pre-restore backup...';
        $emergency = $this->createBackup(gzip: false);
        if ($emergency['success']) {
            $log[] = '✓ Emergency backup created: ' . $emergency['filename'];
            Log::channel('backup')->info('BackupService: Emergency backup before restore: ' . $emergency['filename']);
        } else {
            $log[] = '⚠ Emergency backup failed: ' . $emergency['message'];
            Log::channel('backup')->warning('BackupService: Emergency backup failed before restore.');
        }

        // ── Build the mysql restore command ───────────────────────────────
        $mysql    = $check['mysql'];
        $host     = config('database.connections.mysql.host', '127.0.0.1');
        $port     = config('database.connections.mysql.port', '3306');
        $user     = config('database.connections.mysql.username', 'root');
        $password = config('database.connections.mysql.password', '');
        $db       = config('database.connections.mysql.database');

        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s %s %s < %s',
            escapeshellarg($mysql),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $password !== '' ? '--password=' . escapeshellarg($password) : '--password=',
            escapeshellarg($db),
            escapeshellarg($uploadedFilePath)
        );

        $log[] = '⏳ Importing SQL file into database...';

        $output     = [];
        $returnCode = 0;
        exec("{$cmd} 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            $errMsg = implode("\n", $output);
            $log[]  = '✗ Restore FAILED: ' . $errMsg;
            Log::channel('backup')->error("BackupService: restore FAILED. Code={$returnCode}. Output={$errMsg}");

            return [
                'success'          => false,
                'message'          => 'Restore failed. Your data is unchanged. Check logs.',
                'emergency_backup' => $emergency['filename'] ?? null,
                'log'              => $log,
            ];
        }

        $log[] = '✓ Database restored successfully.';
        Log::channel('backup')->info('BackupService: restore SUCCESS.');

        // Clean up the uploaded temp file
        @unlink($uploadedFilePath);

        return [
            'success'          => true,
            'message'          => 'Database restored successfully.',
            'emergency_backup' => $emergency['filename'] ?? null,
            'log'              => $log,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FILE MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * List all backup files, newest first.
     *
     * @return array<int, array{filename: string, size_mb: float, created_at: string}>
     */
    public function listBackupFiles(): array
    {
        $this->ensureBackupFolder();
        $files = Storage::disk($this->disk)->files($this->backupFolder);

        $result = [];
        foreach ($files as $file) {
            $basename = basename($file);
            // Only list .sql and .sql.gz files
            if (!str_ends_with($basename, '.sql') && !str_ends_with($basename, '.sql.gz')) {
                continue;
            }
            $fullPath  = Storage::disk($this->disk)->path($file);
            $result[] = [
                'filename'   => $basename,
                'path'       => $file,
                'size_mb'    => round(filesize($fullPath) / 1024 / 1024, 2),
                'created_at' => Carbon::createFromTimestamp(filemtime($fullPath))->toDateTimeString(),
                'timestamp'  => filemtime($fullPath),
            ];
        }

        // Sort newest first
        usort($result, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

        return $result;
    }

    /**
     * Get the path to the latest backup file (relative to the disk).
     */
    public function getLatestBackupFile(): ?string
    {
        $files = $this->listBackupFiles();
        return $files[0]['path'] ?? null;
    }

    /**
     * Delete a specific backup file by basename.
     */
    public function deleteBackupFile(string $filename): bool
    {
        // Sanitize to prevent path traversal
        $filename = basename($filename);
        $path     = "{$this->backupFolder}/{$filename}";

        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
            Log::channel('backup')->info("BackupService: deleted backup file: {$filename}");
            return true;
        }

        return false;
    }

    /**
     * Remove backup files older than $retentionDays.
     */
    public function cleanupOldBackups(): int
    {
        $deleted   = 0;
        $threshold = Carbon::now()->subDays($this->retentionDays)->timestamp;
        $files     = $this->listBackupFiles();

        foreach ($files as $file) {
            if ($file['timestamp'] < $threshold) {
                $this->deleteBackupFile($file['filename']);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            Log::channel('backup')->info("BackupService: cleanup removed {$deleted} old backup(s).");
        }

        return $deleted;
    }

    /**
     * Return the absolute path to the backup folder, creating it if needed.
     */
    public function ensureBackupFolder(): bool
    {
        $path = Storage::disk($this->disk)->path($this->backupFolder);

        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                Log::channel('backup')->error("BackupService: cannot create backup folder: {$path}");
                return false;
            }
        }

        // Drop a .gitignore so SQL files aren't committed
        $gitignore = $path . DIRECTORY_SEPARATOR . '.gitignore';
        if (!file_exists($gitignore)) {
            file_put_contents($gitignore, "*.sql\n*.sql.gz\n");
        }

        return is_writable($path);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATION
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate an uploaded SQL file.
     *
     * @return array{ok: bool, message: string}
     */
    public function validateSqlFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['ok' => false, 'message' => 'Uploaded file does not exist.'];
        }

        // Size check
        if (filesize($filePath) > $this->maxUploadBytes) {
            $maxMb = $this->maxUploadBytes / 1024 / 1024;
            return ['ok' => false, 'message' => "File exceeds the maximum allowed size ({$maxMb} MB)."];
        }

        if (filesize($filePath) < 10) {
            return ['ok' => false, 'message' => 'Uploaded file is too small to be a valid SQL dump.'];
        }

        // Extension check (allow .sql only)
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            return ['ok' => false, 'message' => 'Only .sql files are accepted. Got: ' . $ext];
        }

        // Magic-byte / content check: first 512 bytes must look like SQL
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['ok' => false, 'message' => 'Cannot open uploaded file for reading.'];
        }
        $header = fread($handle, 512);
        fclose($handle);

        // Binary-content sniff: reject files that look like PHP/executables
        if (str_contains($header, '<?php') || str_contains($header, '<?=') || str_contains($header, '<script')) {
            Log::channel('backup')->warning("BackupService: Dangerous file upload blocked. Path={$filePath}");
            return ['ok' => false, 'message' => 'Security check failed: uploaded file contains disallowed content.'];
        }

        // Must contain SQL-like tokens
        $lowerHeader = strtolower($header);
        $hasSqlToken = str_contains($lowerHeader, 'create') ||
                       str_contains($lowerHeader, 'insert') ||
                       str_contains($lowerHeader, 'drop')   ||
                       str_contains($lowerHeader, '-- mysql') ||
                       str_contains($lowerHeader, '-- dump') ||
                       str_contains($lowerHeader, 'set names');

        if (!$hasSqlToken) {
            return ['ok' => false, 'message' => 'File does not appear to be a valid MySQL dump.'];
        }

        return ['ok' => true, 'message' => 'File is valid.'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Run all pre-flight checks and return a unified result.
     *
     * @param  bool  $needMysql  Whether to also check for the mysql client binary.
     * @return array{ok: bool, message: string, mysqldump: ?string, mysql: ?string}
     */
    protected function preflightCheck(bool $needMysql = false): array
    {
        // DB connection
        if (!$this->testDbConnection()) {
            return [
                'ok'        => false,
                'message'   => 'Database connection failed. Check your .env DB_* settings.',
                'mysqldump' => null,
                'mysql'     => null,
            ];
        }

        // mysqldump binary
        $mysqldump = $this->resolveMysqldumpPath();
        if (!$mysqldump) {
            return [
                'ok'        => false,
                'message'   => 'mysqldump binary not found. Set MYSQLDUMP_PATH in .env or ensure XAMPP/MySQL is installed.',
                'mysqldump' => null,
                'mysql'     => null,
            ];
        }

        // mysql binary (for restore)
        $mysql = null;
        if ($needMysql) {
            $mysql = $this->resolveMysqlPath();
            if (!$mysql) {
                return [
                    'ok'        => false,
                    'message'   => 'mysql binary not found. Set MYSQL_PATH in .env or ensure XAMPP/MySQL is installed.',
                    'mysqldump' => $mysqldump,
                    'mysql'     => null,
                ];
            }
        }

        // Backup folder writable
        if (!$this->ensureBackupFolder()) {
            return [
                'ok'        => false,
                'message'   => 'Backup storage folder is not writable. Check permissions on storage/app/backups.',
                'mysqldump' => $mysqldump,
                'mysql'     => $mysql,
            ];
        }

        return [
            'ok'        => true,
            'message'   => 'All checks passed.',
            'mysqldump' => $mysqldump,
            'mysql'     => $mysql,
        ];
    }
}
