<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $dept = \App\Models\Department::first();

        User::firstOrCreate(
            ['email' => 'superadmin@filetrack.local'],
            [
                'name' => 'Director General',
                'password' => Hash::make('Admin@1234'),
                'role' => 'super_admin',
                'department_id' => $dept?->id,
                'designation_id' => null,
                'is_active' => true,
                'can_create_file' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Director seeded: superadmin@filetrack.local / Admin@1234');
    }
}
