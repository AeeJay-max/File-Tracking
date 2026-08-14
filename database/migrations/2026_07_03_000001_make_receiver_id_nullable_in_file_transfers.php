<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make receiver_id nullable in file_transfers.
 *
 * On a fresh install the base create_file_transfers migration already creates
 * receiver_id as nullable, so this migration is a no-op for new setups.
 *
 * For existing MySQL databases that already have receiver_id as NOT NULL,
 * this migration drops and re-adds the column as nullable.
 *
 * SQLite: silently skipped — SQLite does not support column modification
 * and the column is already nullable from the base migration on test envs.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Already nullable in base migration — skip if column is already nullable.
        // This check prevents errors on fresh installs and SQLite test environments.
        $driver = Schema::getConnection()->getDriverName();

        if (($driver === 'mysql' || $driver === 'mariadb') && $this->isNotNullable()) {
            Schema::table('file_transfers', function (Blueprint $table) {
                $table->dropForeign(['receiver_id']);
                $table->dropColumn('receiver_id');
            });
            Schema::table('file_transfers', function (Blueprint $table) {
                $table->foreignId('receiver_id')
                    ->nullable()
                    ->after('sender_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
        // SQLite / already-nullable columns: nothing to do
    }

    public function down(): void
    {
        // Intentional no-op: reverting to NOT NULL would break department ownership.
    }

    /** Check if receiver_id is currently NOT NULL on MySQL. */
    private function isNotNullable(): bool
    {
        try {
            $col = DB::selectOne(
                "SELECT IS_NULLABLE FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME   = 'file_transfers'
                   AND COLUMN_NAME  = 'receiver_id'"
            );

            return $col && $col->IS_NULLABLE === 'NO';
        } catch (Throwable) {
            return false;
        }
    }
};
