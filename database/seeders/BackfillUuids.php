<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillUuids extends Seeder
{
    public function run(): void
    {
        foreach (['users', 'file_records', 'departments', 'designations'] as $table) {
            DB::table($table)->whereNull('uuid')->orderBy('id')->each(function ($row) use ($table) {
                DB::table($table)->where('id', $row->id)->update(['uuid' => Str::uuid()->toString()]);
            });
            $count = DB::table($table)->whereNotNull('uuid')->count();
            $this->command->info("$table: $count rows now have UUID");
        }
    }
}
