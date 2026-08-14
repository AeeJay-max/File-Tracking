<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Manual on-demand database backup command.
 *
 * Uses the same pure-PDO dump strategy as BackupController and AutoBackup
 * so no external `mysqldump` binary is required.
 *
 * Usage:
 *   php artisan backup:database
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Create an on-demand database backup (stored in storage/app/backups/)';

    public function handle(): int
    {
        try {
            $disk = Storage::disk('local');
            $directory = 'backups';

            if (! $disk->exists($directory)) {
                $disk->makeDirectory($directory);
            }

            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "backup_db_{$timestamp}.sql";
            $path = "{$directory}/{$filename}";

            $sql = $this->dumpDatabase();
            $disk->put($path, $sql);

            AuditLog::create([
                'user_id' => null,
                'action' => 'backup_manual',
                'auditable_type' => 'system',
                'auditable_id' => 0,
                'description' => "Manual backup created: {$filename}",
                'metadata' => ['filename' => $filename, 'size' => strlen($sql)],
            ]);

            Log::info("Manual backup created: {$filename}");
            $this->info("Backup created: {$filename}");
            $this->line("  Stored at: storage/app/{$path}");

            return self::SUCCESS;

        } catch (\Throwable $e) {
            Log::error('Manual backup failed', ['error' => $e->getMessage()]);
            $this->error('Backup failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

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
                        $vals = array_map(
                            fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                            $row
                        );
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
}
