<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scope File Number Uniqueness to Department
 *
 * Ensures the composite unique constraint (department_id, file_number) is
 * in place and removes any legacy global unique index on file_number alone.
 *
 * On a fresh install, create_file_records_table already creates the composite
 * unique, so this migration acts as a safety net for databases upgraded from
 * an older schema that had a global unique constraint.
 *
 * Also adds the (current_department_id, file_number) non-unique index used
 * by queries that look up files by their current holding department.
 *
 * All DDL operations are guarded with existence checks to make this migration
 * fully idempotent — safe to run on both fresh installs and upgrades.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('file_records')) {
            return;
        }

        // ── 1. Drop any legacy GLOBAL unique index on file_number alone ──────────
        // This existed in older versions of the schema before department-scoped
        // uniqueness was introduced. On current fresh installs it does not exist.
        if ($this->indexExists('file_records', 'file_records_file_number_unique')) {
            Schema::table('file_records', function (Blueprint $table) {
                $table->dropUnique('file_records_file_number_unique');
            });
        }

        // ── 2. Add composite UNIQUE (department_id, file_number) if missing ──────
        // The original create_file_records_table migration already adds this
        // on fresh installs, so this block is for upgrade paths only.
        if (! $this->indexExists('file_records', 'file_records_department_id_file_number_unique')) {
            Schema::table('file_records', function (Blueprint $table) {
                $table->unique(
                    ['department_id', 'file_number'],
                    'file_records_department_id_file_number_unique'
                );
            });
        }

        // ── 3. Add (current_department_id, file_number) lookup index if missing ──
        // Used by queries that resolve which department currently holds a file.
        if (! $this->indexExists('file_records', 'file_records_current_department_id_file_number_index')) {
            Schema::table('file_records', function (Blueprint $table) {
                $table->index(
                    ['current_department_id', 'file_number'],
                    'file_records_current_department_id_file_number_index'
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('file_records')) {
            return;
        }

        // Remove the current-department lookup index
        if ($this->indexExists('file_records', 'file_records_current_department_id_file_number_index')) {
            Schema::table('file_records', function (Blueprint $table) {
                $table->dropIndex('file_records_current_department_id_file_number_index');
            });
        }

        // Remove the composite unique — only if it was added by THIS migration
        // (i.e. it was absent before this migration ran). On a fresh install the
        // original migration owns this index, so we leave it alone on rollback.
        // We cannot reliably determine ownership here, so we intentionally leave
        // the composite unique in place to avoid breaking the schema on rollback.
        // A full rollback should drop and recreate the table instead.
    }

    // ── Helper: check whether a named index exists on a table ─────────────────

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: query sqlite_master for the index
            $result = DB::select(
                "SELECT name FROM sqlite_master WHERE type='index' AND name=?",
                [$indexName]
            );

            return ! empty($result);
        }

        // MySQL / MariaDB / PostgreSQL — information_schema
        if (in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
            $database = DB::getDatabaseName();
            $result = DB::select(
                "SELECT 1 FROM information_schema.statistics
                  WHERE table_schema = ? AND table_name = ? AND index_name = ?
                  LIMIT 1",
                [$database, $table, $indexName]
            );

            return ! empty($result);
        }

        // Unknown driver — assume index does not exist (safe default)
        return false;
    }
};
