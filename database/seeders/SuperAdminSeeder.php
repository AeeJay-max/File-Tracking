<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin has NO department (system-wide access)
        User::firstOrCreate(
            ['email' => 'superadmin@filetrack.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@1234'),
                'role' => 'super_admin',
                'department_id' => null,    // intentionally null — system-wide
                'designation_id' => null,
                'is_active' => true,
                'can_create_file' => false,   // super admin cannot create files (per SRS)
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Super Admin seeded: superadmin@filetrack.local / Admin@1234');
    }
}
