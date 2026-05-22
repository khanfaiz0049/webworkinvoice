<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * BackupController
 *
 * Handles all HTTP interactions for the backup & restore system:
 * - Health Check / Diagnostics dashboard
 * - Trigger a backup download
 * - Upload & restore a SQL file
 * - List stored backups
 * - Delete a stored backup
 *
 * Uses pure PHP (PDO) — no shell commands.
 * Safe for Hostinger and shared hosting environments.
 */
class BackupController extends Controller
{
    public function __construct(protected BackupService $backupService) {}

    // ─────────────────────────────────────────────────────────────────────────
    // HEALTH CHECK DASHBOARD
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Show the backup dashboard with diagnostics.
     */
    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('profile.edit');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BACKUP
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a backup and stream it as a download response.
     *
     * POST /backup/download
     */
    public function download(Request $request): \Illuminate\Http\RedirectResponse
    {
        $gzip   = (bool) $request->input('gzip', false);
        $result = $this->backupService->createBackup(gzip: $gzip);

        if (!$result['success']) {
            return redirect()->route('profile.edit')
                ->with('error', $result['message']);
        }

        return redirect()->route('backup.files.download', $result['filename']);
    }

    /**
     * Create a backup and store it (no download), then redirect back.
     *
     * POST /backup/store
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $gzip   = (bool) $request->input('gzip', false);
        $result = $this->backupService->createBackup(gzip: $gzip);

        if ($result['success']) {
            return redirect()->route('profile.edit')
                ->with('success', $result['message']);
        }

        return redirect()->route('profile.edit')
            ->with('error', $result['message']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RESTORE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Accept an uploaded .sql file and restore the database.
     *
     * POST /backup/restore
     */
    public function restore(Request $request): \Illuminate\Http\RedirectResponse
    {
        // ── Validate the HTTP upload ───────────────────────────────────────
        $request->validate([
            'sql_file' => [
                'required',
                'file',
                'mimes:sql',           // enforces .sql extension
                'max:51200',           // 50 MB in kilobytes
            ],
        ], [
            'sql_file.required' => 'Please select a .sql backup file to upload.',
            'sql_file.mimes'    => 'Only .sql files are accepted.',
            'sql_file.max'      => 'The file must not exceed 50 MB.',
        ]);

        // ── Save the file to a temp location ──────────────────────────────
        $uploaded = $request->file('sql_file');
        $tempPath = storage_path('app/backups/restore_tmp_' . time() . '.sql');

        // Ensure folder exists
        $this->backupService->ensureBackupFolder();

        if (!$uploaded->move(dirname($tempPath), basename($tempPath))) {
            return redirect()->route('profile.edit')
                ->with('error', 'Failed to save the uploaded file. Check storage permissions.');
        }

        // ── Run the restore ────────────────────────────────────────────────
        $result = $this->backupService->restoreBackup($tempPath);

        $log = $result['log'] ?? [];

        if ($result['success']) {
            session()->flash('backup_log', $log);
            return redirect()->route('profile.edit')
                ->with('success', $result['message']);
        }

        session()->flash('backup_log', $log);
        return redirect()->route('profile.edit')
            ->with('error', $result['message']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FILE MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Download a previously-stored backup file.
     *
     * GET /backup/files/{filename}
     */
    public function downloadFile(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        // Sanitize filename to prevent path traversal
        $filename = basename($filename);
        $path     = 'backups/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Backup file not found.');
        }

        $fullPath = Storage::disk('local')->path($path);
        $mime     = str_ends_with($filename, '.gz') ? 'application/gzip' : 'application/sql';

        return response()->download($fullPath, $filename, [
            'Content-Type'        => $mime,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Delete a stored backup file.
     *
     * DELETE /backup/files/{filename}
     */
    public function destroyFile(string $filename): \Illuminate\Http\RedirectResponse
    {
        $filename = basename($filename);

        if ($this->backupService->deleteBackupFile($filename)) {
            return redirect()->route('profile.edit')
                ->with('success', "Backup file '{$filename}' deleted.");
        }

        return redirect()->route('profile.edit')
            ->with('error', "File '{$filename}' not found or could not be deleted.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HEALTH CHECK (JSON API — for AJAX/diagnostic pings)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return health-check data as JSON.
     *
     * GET /backup/health
     */
    public function health(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->backupService->healthCheck());
    }
}
