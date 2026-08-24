<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->after('id')->constrained('folders')->nullOnDelete();
            $table->timestamp('return_deadline')->nullable()->after('completed_at');
            $table->boolean('is_urgent')->default(false)->after('return_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropColumn(['folder_id', 'return_deadline', 'is_urgent']);
        });
    }
};
