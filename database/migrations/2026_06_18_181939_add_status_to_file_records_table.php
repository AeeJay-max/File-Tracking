<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a `status` column to file_records.
 *
 * Cross-database compatible:
 *  - MySQL/MariaDB: uses ENUM for strict database-level enforcement.
 *  - SQLite:        uses TEXT (SQLite has no ENUM type); application-level
 *                   validation in FileRecordController enforces valid values.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement(
                'ALTER TABLE file_records ADD COLUMN status '.
                "ENUM('draft','active','pending_transfer','archived') ".
                "NOT NULL DEFAULT 'active'"
            );
        } else {
            // SQLite / other databases
            Schema::table('file_records', function (Blueprint $table) {
                $table->string('status', 30)->default('active')->after('remarks');
            });
        }
    }

    public function down(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
