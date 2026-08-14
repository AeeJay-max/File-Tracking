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
        $adminDept = Department::where('code', 'ADMIN')->first();
        $itDept = Department::where('code', 'IT')->first();
        $adminDesig = Designation::where('name', 'Department Head')->first();
        $userDesig = Designation::where('name', 'Officer')->first();

        if (! $adminDept || ! $itDept) {
            $this->command->warn('Departments missing — run DepartmentSeeder first.');

            return;
        }

        // Department Admin
        User::firstOrCreate(
            ['email' => 'admin@filetrack.local'],
            [
                'name' => 'Department Admin',
                'password' => Hash::make('Admin@1234'),
                'role' => 'admin',
                'department_id' => $adminDept->id,
                'designation_id' => $adminDesig?->id,
                'is_active' => true,
                'can_create_file' => true,
                'email_verified_at' => now(),
            ]
        );

        // Sample User 1
        User::firstOrCreate(
            ['email' => 'user1@filetrack.local'],
            [
                'name' => 'Alice Sample',
                'password' => Hash::make('User@1234'),
                'role' => 'user',
                'department_id' => $adminDept->id,
                'designation_id' => $userDesig?->id,
                'is_active' => true,
                'can_create_file' => true,
                'email_verified_at' => now(),
            ]
        );

        // Sample User 2 (different department for cross-dept transfer testing)
        User::firstOrCreate(
            ['email' => 'user2@filetrack.local'],
            [
                'name' => 'Bob Sample',
                'password' => Hash::make('User@1234'),
                'role' => 'user',
                'department_id' => $itDept->id,
                'designation_id' => null,
                'is_active' => true,
                'can_create_file' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Sample users seeded. Credentials:');
        $this->command->line('  Admin:  admin@filetrack.local   / Admin@1234');
        $this->command->line('  User1:  user1@filetrack.local   / User@1234');
        $this->command->line('  User2:  user2@filetrack.local   / User@1234');
    }
}
