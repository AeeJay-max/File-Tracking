<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * 1. Adds UUID columns to users, file_records, departments, designations.
 * 2. Adds only the performance indexes that are NOT already created by
 *    foreign-key constraints or unique constraints in the base migrations.
 *
 * Indexes NOT added here (already exist from base migrations):
 *  - file_records_department_id_file_number_* → covered by ->unique(['department_id','file_number']) in create_file_records
 *  - file_records_department_id_*   → covered by ->foreignId() FK index
 *  - file_records_created_by_*      → covered by ->foreignId() FK index
 *  - file_movements_file_id_*       → covered by ->foreignId() FK index
 *  - file_movements_from_user_*     → covered by ->foreignId() FK index
 *  - file_movements_to_user_*       → covered by ->foreignId() FK index
 *  - users_department_id_*          → covered by ->foreignId() FK index
 *  - audit_logs_user_id_*           → covered by ->foreignId() FK index
 *
 * Indexes added here (no FK or unique already covers them):
 *  - file_records: status, current_user_id, created_at
 *  - file_movements: action, created_at
 *  - users: role
 *  - audit_logs: action, created_at
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── UUIDs ─────────────────────────────────────────────────────────────
        // Guarded by hasColumn — safe for both fresh installs and existing DBs.

        foreach (['users', 'file_records', 'departments', 'designations'] as $table) {
            if (Schema::hasColumn($table, 'uuid')) {
                continue; // already added (e.g. by 000003 or a previous run)
            }

            Schema::table($table, function (Blueprint $t) {
                $t->string('uuid', 36)->nullable()->after('id');
            });

            DB::table($table)->orderBy('id')->chunk(200, function ($rows) use ($table) {
                foreach ($rows as $row) {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['uuid' => Str::uuid()->toString()]);
                }
            });

            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->string('uuid', 36)->nullable(false)->change();
                $t->unique('uuid', "{$table}_uuid_unique");
            });
        }

        // ── PERFORMANCE INDEXES ───────────────────────────────────────────────
        // Only add indexes that are NOT already covered by FK constraints or
        // unique keys created in the base migrations.
        // Each call is wrapped so a pre-existing index never breaks the migration.

        // file_records — status, current_user_id, created_at
        // (file_number, department_id already indexed by unique/FK)
        $this->addIndexIfMissing('file_records', 'status',      'file_records_status_index');
        $this->addIndexIfMissing('file_records', 'created_at',  'file_records_created_at_index');

        // current_user_id is added in a later migration — only index it if the column exists
        if (Schema::hasColumn('file_records', 'current_user_id')) {
            $this->addIndexIfMissing('file_records', 'current_user_id', 'file_records_current_user_id_index');
        }

        // file_movements — action, created_at
        // (file_id, from_user, to_user already indexed by FK constraints)
        $this->addIndexIfMissing('file_movements', 'action',     'file_movements_action_index');
        $this->addIndexIfMissing('file_movements', 'created_at', 'file_movements_created_at_index');

        // users — role
        // (department_id, designation_id already indexed by FK constraints)
        $this->addIndexIfMissing('users', 'role', 'users_role_index');

        // audit_logs — action, created_at
        // (user_id already indexed by FK constraint)
        if (Schema::hasTable('audit_logs')) {
            $this->addIndexIfMissing('audit_logs', 'action',     'audit_logs_action_index');
            $this->addIndexIfMissing('audit_logs', 'created_at', 'audit_logs_created_at_index');
        }
    }

    public function down(): void
    {
        // Drop performance indexes (only the ones we added)
        $indexes = [
            'file_records'  => ['file_records_status_index', 'file_records_current_user_id_index', 'file_records_created_at_index'],
            'file_movements' => ['file_movements_action_index', 'file_movements_created_at_index'],
            'users'          => ['users_role_index'],
            'audit_logs'     => ['audit_logs_action_index', 'audit_logs_created_at_index'],
        ];

        foreach ($indexes as $table => $names) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($names as $name) {
                $this->dropIndexIfExists($table, $name);
            }
        }

        // Drop UUID columns
        foreach (['users', 'file_records', 'departments', 'designations'] as $table) {
            if (Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $this->dropIndexIfExists($table, "{$table}_uuid_unique");
                    $t->dropColumn('uuid');
                });
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Add a plain index on $column, but only if no index with $name already exists.
     *
     * Works on both MySQL and SQLite:
     *  - MySQL:  uses information_schema to check existence before adding.
     *  - SQLite: uses PRAGMA index_list to check existence before adding.
     *
     * Falls back to try/catch as a last resort so the migration never crashes.
     */
    private function addIndexIfMissing(string $table, string $column, string $name): void
    {
        if ($this->indexExists($table, $name)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($column, $name) {
                $t->index($column, $name);
            });
        } catch (\Throwable) {
            // Index appeared between the check and the add (race) or already exists
        }
    }

    /**
     * Drop an index by name if it exists — cross-database safe.
     */
    private function dropIndexIfExists(string $table, string $name): void
    {
        if (! $this->indexExists($table, $name)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($name) {
                $t->dropIndex($name);
            });
        } catch (\Throwable) {
            // Already gone
        }
    }

    /**
     * Check whether an index named $name exists on $table.
     * Cross-database: MySQL uses information_schema; SQLite uses PRAGMA.
     */
    private function indexExists(string $table, string $name): bool
    {
        $driver = DB::getDriverName();

        try {
            if ($driver === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list(\"{$table}\")");
                foreach ($indexes as $idx) {
                    if ($idx->name === $name) {
                        return true;
                    }
                }
                return false;
            }

            // MySQL / MariaDB / PostgreSQL — use information_schema
            $result = DB::selectOne(
                "SELECT COUNT(*) as cnt
                 FROM information_schema.STATISTICS
                 WHERE table_schema = DATABASE()
                   AND table_name   = ?
                   AND index_name   = ?",
                [$table, $name]
            );
            return (int) ($result->cnt ?? 0) > 0;

        } catch (\Throwable) {
            // If we can't check, assume it doesn't exist and let try/catch handle it
            return false;
        }
    }
};
