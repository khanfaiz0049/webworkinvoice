<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

/**
 * BackupService
 *
 * Handles all database backup and restore operations using PURE PHP.
 * No exec(), shell_exec(), system(), passthru(), or proc_open() calls.
 *
 * This is fully compatible with:
 * - Hostinger shared/business hosting (no shell access needed)
 * - Servers where dangerous PHP functions are disabled
 * - Environments with aggressive malware scanners (Monarx)
 *
 * Features:
 * - Creates timestamped SQL backups via PDO queries
 * - Validates and safely restores SQL files
 * - Cleans up backups older than 30 days
 * - Logs all operations to a dedicated channel
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

    /** Maximum number of rows to fetch per batch (memory safety) */
    protected int $batchSize = 1000;

    // ─────────────────────────────────────────────────────────────────────────
    // ENVIRONMENT DETECTION
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Detect whether the app is running on Windows or Linux.
     */
    public function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VERIFICATION / HEALTH CHECK
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Run a full diagnostic and return a structured status array.
     */
    public function healthCheck(): array
    {
        $folderPath = Storage::disk($this->disk)->path($this->backupFolder);
        $folderOk   = is_dir($folderPath) ? is_writable($folderPath) : $this->ensureBackupFolder();
        $dbOk       = $this->testDbConnection();
        $dbSize     = $dbOk ? $this->getDatabaseSize() : null;
        $latestFile = $this->getLatestBackupFile();
        $latestDate = $latestFile
            ? Carbon::createFromTimestamp(filemtime(Storage::disk($this->disk)->path($latestFile)))
            : null;
        $backupCount = count($this->listBackupFiles());

        return [
            'os'                 => $this->isWindows() ? 'Windows' : PHP_OS,
            'method'             => 'Pure PHP (PDO)',
            'mysql_connection'   => $dbOk,
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
    // BACKUP (PURE PHP — NO SHELL COMMANDS)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a database backup using pure PHP and PDO.
     *
     * Generates a standards-compliant SQL dump file without requiring
     * mysqldump or any shell command execution.
     *
     * @param  bool  $gzip  Whether to gzip-compress the output file.
     * @return array{success: bool, message: string, filename: ?string, path: ?string}
     */
    public function createBackup(bool $gzip = false): array
    {
        Log::channel('backup')->info('BackupService: Starting pure-PHP backup. gzip=' . ($gzip ? 'true' : 'false'));

        // ── Pre-flight checks ──────────────────────────────────────────────
        if (!$this->testDbConnection()) {
            return [
                'success'  => false,
                'message'  => 'Database connection failed. Check your .env DB_* settings.',
                'filename' => null,
                'path'     => null,
            ];
        }

        $this->ensureBackupFolder();
        $this->cleanupOldBackups();

        // ── Build output filename ──────────────────────────────────────────
        $db        = config('database.connections.mysql.database');
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $ext       = $gzip ? 'sql.gz' : 'sql';
        $filename  = "backup_{$db}_{$timestamp}.{$ext}";
        $filePath  = Storage::disk($this->disk)->path("{$this->backupFolder}/{$filename}");

        try {
            // ── Generate the SQL dump ──────────────────────────────────────
            $sql = $this->generateSqlDump($db);

            if (empty($sql)) {
                return [
                    'success'  => false,
                    'message'  => 'Backup failed: generated SQL dump is empty.',
                    'filename' => null,
                    'path'     => null,
                ];
            }

            // ── Write to file ──────────────────────────────────────────────
            if ($gzip) {
                $gz = gzopen($filePath, 'wb9');
                if (!$gz) {
                    throw new \RuntimeException("Cannot open gzip file for writing: {$filePath}");
                }
                gzwrite($gz, $sql);
                gzclose($gz);
            } else {
                if (file_put_contents($filePath, $sql) === false) {
                    throw new \RuntimeException("Cannot write backup file: {$filePath}");
                }
            }

            // ── Validate result ────────────────────────────────────────────
            if (!file_exists($filePath) || filesize($filePath) < 50) {
                return [
                    'success'  => false,
                    'message'  => 'Backup failed: output file is too small or missing.',
                    'filename' => null,
                    'path'     => null,
                ];
            }

            $sizeMb = round(filesize($filePath) / 1024 / 1024, 2);
            Log::channel('backup')->info("BackupService: backup SUCCESS. File={$filename}, Size={$sizeMb}MB");

            return [
                'success'  => true,
                'message'  => "Backup created successfully: {$filename} ({$sizeMb} MB)",
                'filename' => $filename,
                'path'     => $filePath,
            ];

        } catch (\Throwable $e) {
            Log::channel('backup')->error('BackupService: backup FAILED: ' . $e->getMessage());
            // Clean up partial file
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            return [
                'success'  => false,
                'message'  => 'Backup failed: ' . $e->getMessage(),
                'filename' => null,
                'path'     => null,
            ];
        }
    }

    /**
     * Generate a complete SQL dump string using PDO.
     *
     * Produces output compatible with MySQL/MariaDB import, including:
     * - SET statements for character encoding
     * - DROP TABLE IF EXISTS + CREATE TABLE statements
     * - Batched INSERT statements for data
     */
    protected function generateSqlDump(string $database): string
    {
        $pdo = DB::connection()->getPdo();
        $lines = [];

        // ── Header ─────────────────────────────────────────────────────────
        $lines[] = "-- ─────────────────────────────────────────────────────────";
        $lines[] = "-- Database Backup: {$database}";
        $lines[] = "-- Generated: " . Carbon::now()->toDateTimeString();
        $lines[] = "-- Method: Pure PHP (PDO) — No shell commands";
        $lines[] = "-- ─────────────────────────────────────────────────────────";
        $lines[] = "";
        $lines[] = "SET NAMES utf8mb4;";
        $lines[] = "SET FOREIGN_KEY_CHECKS = 0;";
        $lines[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
        $lines[] = "SET AUTOCOMMIT = 0;";
        $lines[] = "START TRANSACTION;";
        $lines[] = "";

        // ── Get all tables ─────────────────────────────────────────────────
        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $lines[] = "-- ──────────────────────────────────────────────────────";
            $lines[] = "-- Table: `{$table}`";
            $lines[] = "-- ──────────────────────────────────────────────────────";
            $lines[] = "";

            // DROP + CREATE
            $lines[] = "DROP TABLE IF EXISTS `{$table}`;";
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $lines[] = $createStmt['Create Table'] . ";";
            $lines[] = "";

            // DATA — fetch in batches to conserve memory
            $rowCount = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();

            if ($rowCount === 0) {
                $lines[] = "-- (empty table)";
                $lines[] = "";
                continue;
            }

            $offset = 0;
            while ($offset < $rowCount) {
                $stmt = $pdo->prepare("SELECT * FROM `{$table}` LIMIT :limit OFFSET :offset");
                $stmt->bindValue(':limit', $this->batchSize, \PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if (empty($rows)) {
                    break;
                }

                // Get column names from the first row
                $columns = array_keys($rows[0]);
                $columnList = implode('`, `', $columns);

                foreach ($rows as $row) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = $pdo->quote($value);
                        }
                    }
                    $valueList = implode(', ', $values);
                    $lines[] = "INSERT INTO `{$table}` (`{$columnList}`) VALUES ({$valueList});";
                }

                $offset += $this->batchSize;
            }

            $lines[] = "";
        }

        // ── Footer ─────────────────────────────────────────────────────────
        $lines[] = "SET FOREIGN_KEY_CHECKS = 1;";
        $lines[] = "COMMIT;";
        $lines[] = "";
        $lines[] = "-- Backup complete.";

        return implode("\n", $lines);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RESTORE (PURE PHP — NO SHELL COMMANDS)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Restore the database from an uploaded SQL file using PDO.
     *
     * Creates an emergency backup first, validates the file, then imports
     * using PHP's PDO — no mysql CLI required.
     *
     * @param  string  $uploadedFilePath  Absolute path to the uploaded .sql file.
     * @return array{success: bool, message: string, emergency_backup: ?string, log: string[]}
     */
    public function restoreBackup(string $uploadedFilePath): array
    {
        $log = [];
        Log::channel('backup')->info("BackupService: Starting pure-PHP restore from: {$uploadedFilePath}");

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

        // ── Pre-flight DB check ────────────────────────────────────────────
        if (!$this->testDbConnection()) {
            return [
                'success'          => false,
                'message'          => 'Database connection failed. Check your .env DB_* settings.',
                'emergency_backup' => null,
                'log'              => ['Database connection failed.'],
            ];
        }
        $log[] = '✓ Database connection verified.';

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

        // ── Execute the SQL file using PDO ─────────────────────────────────
        $log[] = '⏳ Importing SQL file into database...';

        try {
            $sql = file_get_contents($uploadedFilePath);

            if ($sql === false || trim($sql) === '') {
                throw new \RuntimeException('Could not read the SQL file or file is empty.');
            }

            $pdo = DB::connection()->getPdo();

            // Disable foreign key checks during restore
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->exec("SET NAMES utf8mb4");

            // Split the SQL file into individual statements
            $statements = $this->splitSqlStatements($sql);
            $executed = 0;
            $errors = [];

            foreach ($statements as $statement) {
                $trimmed = trim($statement);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                try {
                    $pdo->exec($trimmed);
                    $executed++;
                } catch (\PDOException $e) {
                    // Log but continue — some statements may be MySQL-version specific
                    $errors[] = substr($trimmed, 0, 80) . '... → ' . $e->getMessage();
                    Log::channel('backup')->warning("BackupService: Statement error during restore: " . $e->getMessage());
                }
            }

            // Re-enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            $log[] = "✓ Executed {$executed} statements successfully.";
            if (count($errors) > 0) {
                $log[] = "⚠ " . count($errors) . " statement(s) had errors (non-critical):";
                foreach (array_slice($errors, 0, 5) as $err) {
                    $log[] = "   → {$err}";
                }
            }

            Log::channel('backup')->info("BackupService: restore SUCCESS. {$executed} statements executed.");

        } catch (\Throwable $e) {
            $log[] = '✗ Restore FAILED: ' . $e->getMessage();
            Log::channel('backup')->error("BackupService: restore FAILED: " . $e->getMessage());

            return [
                'success'          => false,
                'message'          => 'Restore failed: ' . $e->getMessage(),
                'emergency_backup' => $emergency['filename'] ?? null,
                'log'              => $log,
            ];
        }

        // Clean up the uploaded temp file
        @unlink($uploadedFilePath);

        return [
            'success'          => true,
            'message'          => 'Database restored successfully.',
            'emergency_backup' => $emergency['filename'] ?? null,
            'log'              => $log,
        ];
    }

    /**
     * Split a SQL dump into individual executable statements.
     *
     * Handles multi-line statements, quoted strings with semicolons,
     * and common MySQL dump patterns.
     *
     * @return string[]
     */
    protected function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            // Handle escape sequences inside strings
            if ($inString && $char === '\\' && $i + 1 < $length) {
                $current .= $char . $sql[$i + 1];
                $i++;
                continue;
            }

            // Toggle string mode
            if (!$inString && ($char === "'" || $char === '"')) {
                $inString = true;
                $stringChar = $char;
                $current .= $char;
                continue;
            }

            if ($inString && $char === $stringChar) {
                $inString = false;
                $current .= $char;
                continue;
            }

            // Skip single-line comments
            if (!$inString && $char === '-' && $i + 1 < $length && $sql[$i + 1] === '-') {
                // Skip to end of line
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                continue;
            }

            // Skip multi-line comments
            if (!$inString && $char === '/' && $i + 1 < $length && $sql[$i + 1] === '*') {
                $i += 2;
                while ($i + 1 < $length && !($sql[$i] === '*' && $sql[$i + 1] === '/')) {
                    $i++;
                }
                $i++; // skip the closing '/'
                continue;
            }

            // Statement delimiter
            if (!$inString && $char === ';') {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        // Any remaining content
        $trimmed = trim($current);
        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
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
                       str_contains($lowerHeader, '-- database backup') ||
                       str_contains($lowerHeader, 'set names');

        if (!$hasSqlToken) {
            return ['ok' => false, 'message' => 'File does not appear to be a valid MySQL dump.'];
        }

        return ['ok' => true, 'message' => 'File is valid.'];
    }
}
