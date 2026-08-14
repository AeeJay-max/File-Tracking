<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        // Map of department code → designations
        $map = [
            'ADMIN' => ['Department Head', 'Senior Officer', 'Officer', 'Clerk'],
            'HR' => ['HR Manager', 'HR Officer', 'Recruitment Officer', 'HR Clerk'],
            'FIN' => ['Finance Manager', 'Accounts Officer', 'Finance Clerk', 'Auditor'],
            'IT' => ['IT Manager', 'Systems Analyst', 'Developer', 'IT Support'],
            'OPS' => ['Operations Manager', 'Operations Officer', 'Field Officer', 'Operations Clerk'],
        ];

        foreach ($map as $code => $names) {
            $dept = Department::where('code', $code)->first();

            if (! $dept) {
                $this->command->warn("Department [{$code}] not found — skipping its designations.");

                continue;
            }

            foreach ($names as $name) {
                Designation::firstOrCreate(
                    ['department_id' => $dept->id, 'name' => $name],
                    ['is_active' => true]
                    // UUID auto-generated in model boot()
                );
            }
        }

        $this->command->info('Designations seeded: '.Designation::count().' total.');
    }
}
