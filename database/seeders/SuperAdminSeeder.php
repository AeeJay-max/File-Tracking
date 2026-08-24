<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'filetrack@mosrac.gov.zw'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Ministry@2018'),
                'role' => 'super_admin',
                'department_id' => null,
                'designation_id' => null,
                'is_active' => true,
                'can_create_file' => true,
                'must_change_password' => false,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Super Admin seeded: filetrack@mosrac.gov.zw / Ministry@2018');
    }
}
