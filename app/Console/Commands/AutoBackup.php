<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AutoBackup extends Command
{
    protected $signature = 'backup:auto';

    protected $description = 'Create an automatic scheduled database backup';

    public function handle(): void
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "backup_db_auto_{$timestamp}.sql";
            $backupPath = 'backups/'.$filename;

            $pdo = DB::connection()->getPdo();
            $output = [];

            $output[] = '-- FileTrack Auto Backup';
            $output[] = '-- Generated: '.now()->toDateTimeString();
            $output[] = 'SET FOREIGN_KEY_CHECKS=0;';
            $output[] = '';

            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $output[] = "\n-- Table: {$table}";
                $output[] = "DROP TABLE IF EXISTS `{$table}`;";
                $output[] = array_values($create)[1].';';
                $output[] = '';

                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
                if (! empty($rows)) {
                    $columns = '`'.implode('`, `', array_keys($rows[0])).'`';
                    foreach ($rows as $row) {
                        $values = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote($v), $row);
                        $output[] = "INSERT INTO `{$table}` ({$columns}) VALUES (".implode(', ', $values).');';
                    }
                }
                $output[] = '';
            }

            $output[] = 'SET FOREIGN_KEY_CHECKS=1;';

            $sql = implode("\n", $output);
            Storage::disk('local')->put($backupPath, $sql);

            // Keep only the last 7 auto backups
            $this->pruneOldBackups();

            AuditLog::create([
                'user_id' => null,
                'action' => 'backup_auto',
                'auditable_type' => 'system',
                'auditable_id' => 0,
                'description' => "Auto backup created: {$filename}",
                'metadata' => ['filename' => $filename, 'size' => strlen($sql)],
            ]);

            Log::info("Auto backup created: {$filename}");
            $this->info("Backup created: {$filename}");

        } catch (\Throwable $e) {
            Log::error('Auto backup failed', ['error' => $e->getMessage()]);
            $this->error('Backup failed: '.$e->getMessage());
        }
    }

    private function pruneOldBackups(): void
    {
        $disk = Storage::disk('local');
        $files = collect($disk->exists('backups') ? $disk->files('backups') : [])
            ->filter(fn ($f) => str_contains(basename($f), '_auto_'))
            ->sortByDesc(fn ($f) => $disk->lastModified($f))
            ->values();

        // Delete all but the newest 7
        $files->slice(7)->each(fn ($f) => $disk->delete($f));
    }
}
