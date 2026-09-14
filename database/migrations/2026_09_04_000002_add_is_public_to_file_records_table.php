<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('status');
            $table->index('is_public', 'idx_file_rec_is_public');
        });
    }

    public function down(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->dropIndex('idx_file_rec_is_public');
            $table->dropColumn('is_public');
        });
    }
};
