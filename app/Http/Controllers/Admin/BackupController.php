<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    // NOTE: Authorization is enforced at the ROUTE level via:
    //   ->middleware(['auth', 'role:super_admin', 'no.cache'])
    // Do NOT use $this->middleware() — removed in Laravel 12.

    private const BACKUP_DISK = 'local';

    private const BACKUP_DIR = 'backups';

    /**
     * Show backup history page.
     */
    public function index()
    {
        // Ensure the backups directory exists
        $disk = Storage::disk(self::BACKUP_DISK);
        if (! $disk->exists(self::BACKUP_DIR)) {
            $disk->makeDirectory(self::BACKUP_DIR);
        }

        $backups = $this->getBackupList();

        return view('admin.backup.index', compact('backups'));
    }

    /**
     * Create a new database backup.
     */
    public function create(Request $request)
    {
        try {
            $disk = Storage::disk(self::BACKUP_DISK);
            if (! $disk->exists(self::BACKUP_DIR)) {
                $disk->makeDirectory(self::BACKUP_DIR);
            }

            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "backup_db_{$timestamp}.sql";
            $backupPath = self::BACKUP_DIR.'/'.$filename;

            $sql = $this->dumpDatabase();
            $disk->put($backupPath, $sql);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'backup_created',
                'auditable_type' => 'system',
                'auditable_id' => 0,
                'description' => "Database backup created: {$filename}",
                'metadata' => [
                    'filename' => $filename,
                    'size' => strlen($sql),
                    'ip' => $request->ip(),
                ],
            ]);

            Log::info('Backup created', ['filename' => $filename, 'user_id' => Auth::id()]);

            return redirect()->route('admin.backup.index')
                ->with('success', "Backup created successfully: {$filename}");

        } catch (\Throwable $e) {
            Log::error('Backup creation failed: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.backup.index')
                ->with('error', 'Backup failed: '.$e->getMessage());
        }
    }

    /**
     * Download a backup file.
     */
    public function download(string $filename)
    {
        $filename = basename($filename); // prevent path traversal
        $path = self::BACKUP_DIR.'/'.$filename;
        $disk = Storage::disk(self::BACKUP_DISK);

        if (! $disk->exists($path)) {
            Log::warning('Backup download: file not found', ['filename' => $filename]);
            abort(404, 'Backup file not found.');
        }

        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'backup_downloaded',
                'auditable_type' => 'system',
                'auditable_id' => 0,
                'description' => "Backup downloaded: {$filename}",
                'metadata' => ['filename' => $filename, 'ip' => request()->ip()],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Backup audit log failed', ['error' => $e->getMessage()]);
        }

        return $disk->download($path, $filename);
    }

    /**
     * Delete a backup file.
     */
    public function destroy(Request $request, string $filename)
    {
        $filename = basename($filename);
        $path = self::BACKUP_DIR.'/'.$filename;
        $disk = Storage::disk(self::BACKUP_DISK);

        if ($disk->exists($path)) {
            $disk->delete($path);

            try {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'backup_deleted',
                    'auditable_type' => 'system',
                    'auditable_id' => 0,
                    'description' => "Backup deleted: {$filename}",
                    'metadata' => ['filename' => $filename, 'ip' => $request->ip()],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Backup delete audit failed', ['error' => $e->getMessage()]);
            }

            Log::info('Backup deleted', ['filename' => $filename, 'user_id' => Auth::id()]);
        }

        return redirect()->route('admin.backup.index')
            ->with('success', "Backup deleted: {$filename}");
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────

    private function getBackupList(): array
    {
        $disk = Storage::disk(self::BACKUP_DISK);
        $files = $disk->exists(self::BACKUP_DIR)
            ? $disk->files(self::BACKUP_DIR)
            : [];

        $backups = [];
        foreach ($files as $file) {
            if (! str_ends_with($file, '.sql')) {
                continue;
            } // only show SQL files

            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'size' => $this->formatBytes($disk->size($file)),
                'size_bytes' => $disk->size($file),
                'created_at' => Carbon::createFromTimestamp($disk->lastModified($file)),
                'type' => str_contains($filename, '_db_') ? 'Database' : 'Files',
                'download_url' => route('admin.backup.download', ['filename' => $filename]),
                'delete_url' => route('admin.backup.destroy', ['filename' => $filename]),
            ];
        }

        usort($backups, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    /**
     * Pure PDO database dump — no external mysqldump binary required.
     */
    private function dumpDatabase(): string
    {
        $pdo = DB::connection()->getPdo();
        $config = config('database.connections.'.config('database.default'));
        $output = [];

        $output[] = '-- FileTrack Database Backup';
        $output[] = '-- Generated: '.now()->toDateTimeString();
        $output[] = '-- Database: '.($config['database'] ?? 'unknown');
        $output[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $output[] = '';

        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql = array_values($createRow)[1];

            $output[] = '';
            $output[] = '-- --------------------------------------------------------';
            $output[] = "-- Table: `{$table}`";
            $output[] = '-- --------------------------------------------------------';
            $output[] = "DROP TABLE IF EXISTS `{$table}`;";
            $output[] = $createSql.';';
            $output[] = '';

            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            if (! empty($rows)) {
                $columns = '`'.implode('`, `', array_keys($rows[0])).'`';
                foreach (array_chunk($rows, 100) as $chunk) {
                    $valueGroups = [];
                    foreach ($chunk as $row) {
                        $vals = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $row);
                        $valueGroups[] = '('.implode(', ', $vals).')';
                    }
                    $output[] = "INSERT INTO `{$table}` ({$columns}) VALUES";
                    $output[] = implode(",\n", $valueGroups).';';
                }
                $output[] = '';
            }
        }

        $output[] = 'SET FOREIGN_KEY_CHECKS=1;';

        return implode("\n", $output);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
