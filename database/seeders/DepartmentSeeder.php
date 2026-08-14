<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Administration',         'code' => 'ADMIN', 'is_active' => true],
            ['name' => 'Human Resources',         'code' => 'HR',    'is_active' => true],
            ['name' => 'Finance',                 'code' => 'FIN',   'is_active' => true],
            ['name' => 'Information Technology',  'code' => 'IT',    'is_active' => true],
            ['name' => 'Operations',              'code' => 'OPS',   'is_active' => true],
        ];

        foreach ($departments as $dept) {
            // Use firstOrCreate to be idempotent — safe to run multiple times
            Department::firstOrCreate(
                ['code' => $dept['code']],
                ['name' => $dept['name'], 'is_active' => $dept['is_active']]
                // UUID is auto-generated in model boot()
            );
        }

        $this->command->info('Departments seeded: '.Department::count().' total.');
    }
}
