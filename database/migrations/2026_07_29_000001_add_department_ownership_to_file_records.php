<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Department-Owned Files — Schema
 *
 * Adds `current_department_id` to file_records so that a department can own
 * a file independently of any user. Also adds `pending_assignment` to the
 * status column.
 *
 * Cross-database compatible: MySQL uses ALTER TABLE MODIFY COLUMN to extend
 * the enum; SQLite recreates the column via string (SQLite has no native enum).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Add current_department_id FK
        Schema::table('file_records', function (Blueprint $table) {
            $table->unsignedBigInteger('current_department_id')
                  ->nullable()
                  ->after('department_id');

            $table->foreign('current_department_id')
                  ->references('id')
                  ->on('departments')
                  ->nullOnDelete();

            // Supports lookups by (current department + file number).
            $table->index(['current_department_id', 'file_number'], 'file_records_current_department_id_file_number_index');
        });

        // 2. Backfill: every existing file's current dept = its registered dept
        DB::table('file_records')->whereNull('current_department_id')
            ->update(['current_department_id' => DB::raw('department_id')]);

        // 3. Extend the status column to include pending_assignment
        //    MySQL/MariaDB: ALTER TABLE … MODIFY COLUMN (enum extension)
        //    SQLite: string column — no ENUM type, accepts any value
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement(
                "ALTER TABLE file_records MODIFY COLUMN status ".
                "ENUM('draft','active','pending_transfer','pending_assignment','archived') ".
                "NOT NULL DEFAULT 'active'"
            );
        }
        // SQLite uses TEXT for enum columns — no ALTER needed;
        // the application-level validation enforces valid values.
    }

    public function down(): void
    {
        // Restore status enum without pending_assignment (MySQL/MariaDB only)
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement(
                "ALTER TABLE file_records MODIFY COLUMN status ".
                "ENUM('draft','active','pending_transfer','archived') ".
                "NOT NULL DEFAULT 'active'"
            );
        }

        Schema::table('file_records', function (Blueprint $table) {
            $table->dropIndex('file_records_current_department_id_file_number_index');
            $table->dropForeign(['current_department_id']);
            $table->dropColumn('current_department_id');
        });
    }
};
