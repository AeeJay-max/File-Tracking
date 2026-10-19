<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('file_transfers') && ! Schema::hasColumn('file_transfers', 'carried_by')) {
            Schema::table('file_transfers', function (Blueprint $table) {
                $table->string('carried_by')->nullable()->after('remarks');
            });
        }

        if (Schema::hasTable('file_movements') && ! Schema::hasColumn('file_movements', 'carried_by')) {
            Schema::table('file_movements', function (Blueprint $table) {
                $table->string('carried_by')->nullable()->after('remarks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('file_transfers') && Schema::hasColumn('file_transfers', 'carried_by')) {
            Schema::table('file_transfers', function (Blueprint $table) {
                $table->dropColumn('carried_by');
            });
        }

        if (Schema::hasTable('file_movements') && Schema::hasColumn('file_movements', 'carried_by')) {
            Schema::table('file_movements', function (Blueprint $table) {
                $table->dropColumn('carried_by');
            });
        }
    }
};
