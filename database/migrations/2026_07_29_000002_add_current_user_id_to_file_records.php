<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `current_user_id` to file_records.
 *
 * This column tracks which individual user currently holds the file.
 * NULL means the file is held at department level (pending_assignment).
 *
 * The original migration that added this column was removed during a
 * cleanup pass. This migration restores it.
 *
 * Guarded by hasColumn so it is safe to re-run and safe on fresh installs
 * where the column may already exist via a future consolidated migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('file_records', 'current_user_id')) {
            return; // already present — nothing to do
        }

        Schema::table('file_records', function (Blueprint $table) {
            $table->foreignId('current_user_id')
                  ->nullable()
                  ->after('current_department_id')
                  ->constrained('users')
                  ->nullOnDelete();

            // Add performance index here — this is the migration that owns
            // the column, so the index is guaranteed to be created after
            // the column exists. (add_uuids_and_indexes runs earlier and
            // guards against the column being absent.)
            $table->index('current_user_id', 'file_records_current_user_id_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('file_records', 'current_user_id')) {
            return;
        }

        Schema::table('file_records', function (Blueprint $table) {
            $table->dropForeign(['current_user_id']);
            $table->dropIndex('file_records_current_user_id_index');
            $table->dropColumn('current_user_id');
        });
    }
};
