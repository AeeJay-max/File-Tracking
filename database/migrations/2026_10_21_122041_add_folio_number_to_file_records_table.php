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
            $table->string('folio_number')->nullable()->after('file_number');
        });

        \Illuminate\Support\Facades\DB::table('file_records')
            ->whereNull('folio_number')
            ->update(['folio_number' => \Illuminate\Support\Facades\DB::raw('file_number')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_records', function (Blueprint $table) {
            $table->dropColumn('folio_number');
        });
    }
};
