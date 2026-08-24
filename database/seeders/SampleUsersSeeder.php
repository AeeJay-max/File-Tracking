<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $recDept   = Department::where('code', 'REC')->first();
        $execDept  = Department::where('code', 'EXEC')->first() ?? Department::where('code', 'ADMIN')->first();
        $adminDept = Department::where('code', 'ADMIN')->first();

        $recAdminDesig = Designation::where('name', 'Records Admin')->first();
        $recOfficerDesig = Designation::where('name', 'Records Officer')->first();
        $permSecDesig  = Designation::where('name', 'Permanent Secretary')->first();

        // 1. Records Admin (creates/assigns & routes all files)
        if ($recDept) {
            User::firstOrCreate(
                ['email' => 'records.admin@filetrack.local'],
                [
                    'name' => 'Records Admin',
                    'password' => Hash::make('Admin@1234'),
                    'role' => 'admin',
                    'department_id' => $recDept->id,
                    'designation_id' => $recAdminDesig?->id,
                    'is_active' => true,
                    'can_create_file' => true,
                    'email_verified_at' => now(),
                ]
            );

            // 2. Records Officer (creates files when assigned by Records Admin, returns to Records Admin)
            User::firstOrCreate(
                ['email' => 'records.officer@filetrack.local'],
                [
                    'name' => 'Records Officer',
                    'password' => Hash::make('User@1234'),
                    'role' => 'user',
                    'department_id' => $recDept->id,
                    'designation_id' => $recOfficerDesig?->id,
                    'is_active' => true,
                    'can_create_file' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // 3. Permanent Secretary (receives file from Records Admin, returns to Records Admin)
        if ($execDept) {
            User::firstOrCreate(
                ['email' => 'permsec@filetrack.local'],
                [
                    'name' => 'Permanent Secretary',
                    'password' => Hash::make('User@1234'),
                    'role' => 'user',
                    'department_id' => $execDept->id,
                    'designation_id' => $permSecDesig?->id,
                    'is_active' => true,
                    'can_create_file' => false,
                    'email_verified_at' => now(),
                ]
            );
        }

        // 4. Departmental Admin & Officers (receive files dispatched by Records)
        if ($adminDept) {
            User::firstOrCreate(
                ['email' => 'admin@filetrack.local'],
                [
                    'name' => 'Department Admin',
                    'password' => Hash::make('Admin@1234'),
                    'role' => 'admin',
                    'department_id' => $adminDept->id,
                    'designation_id' => null,
                    'is_active' => true,
                    'can_create_file' => false,
                    'email_verified_at' => now(),
                ]
            );

            User::firstOrCreate(
                ['email' => 'user1@filetrack.local'],
                [
                    'name' => 'Alice Officer',
                    'password' => Hash::make('User@1234'),
                    'role' => 'user',
                    'department_id' => $adminDept->id,
                    'designation_id' => null,
                    'is_active' => true,
                    'can_create_file' => false,
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('Sample users seeded. Credentials:');
        $this->command->line('  Records Admin: records.admin@filetrack.local / Admin@1234');
        $this->command->line('  Records Officer: records.officer@filetrack.local / User@1234');
        $this->command->line('  Permanent Sec.:  permsec@filetrack.local       / User@1234');
    }
}
