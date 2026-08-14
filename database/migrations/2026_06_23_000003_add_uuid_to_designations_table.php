<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('designations', 'uuid')) {
            Schema::table('designations', function (Blueprint $table) {
                $table->string('uuid', 36)->nullable()->after('id');
            });

            // Backfill existing rows
            DB::table('designations')->orderBy('id')->each(function ($row) {
                DB::table('designations')->where('id', $row->id)->update([
                    'uuid' => Str::uuid()->toString(),
                ]);
            });

            Schema::table('designations', function (Blueprint $table) {
                $table->string('uuid', 36)->nullable(false)->change();
                $table->unique('uuid', 'designations_uuid_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('designations', 'uuid')) {
            Schema::table('designations', function (Blueprint $table) {
                $table->dropUnique('designations_uuid_unique');
                $table->dropColumn('uuid');
            });
        }
    }
};
