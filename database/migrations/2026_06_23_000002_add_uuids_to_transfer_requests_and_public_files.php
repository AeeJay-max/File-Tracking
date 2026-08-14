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
        foreach (['transfer_requests', 'public_files'] as $table) {
            if (! Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('uuid', 36)->nullable()->after('id');
                });

                DB::table($table)->orderBy('id')->each(function ($row) use ($table) {
                    DB::table($table)->where('id', $row->id)->update(['uuid' => Str::uuid()->toString()]);
                });

                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->string('uuid', 36)->nullable(false)->change();
                    $t->unique('uuid', "{$table}_uuid_unique");
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['transfer_requests', 'public_files'] as $table) {
            if (Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->dropUnique("{$table}_uuid_unique");
                    $t->dropColumn('uuid');
                });
            }
        }
    }
};
