<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('file_records', 'completed_at')) {
            Schema::table('file_records', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('file_records', 'completed_at')) {
            Schema::table('file_records', function (Blueprint $table) {
                $table->dropColumn('completed_at');
            });
        }
    }
};
