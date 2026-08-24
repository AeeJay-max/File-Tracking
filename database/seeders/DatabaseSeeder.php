<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== FileTrack System Seeder ===');

        // ── 1. Departments & Designations Structure ───────────────
        $this->call([
            DepartmentSeeder::class,
            DesignationSeeder::class,
        ]);

        // ── 2. Super Admin Account (Only Account Seeded) ──────────
        User::updateOrCreate(
            ['email' => 'filetrack@mosrac.gov.zw'],
            [
                'name'                 => 'Super Admin',
                'password'             => Hash::make('Ministry@2018'),
                'role'                 => 'super_admin',
                'department_id'        => null,
                'designation_id'       => null,
                'is_active'            => true,
                'can_create_file'      => true,
                'must_change_password' => false,
                'email_verified_at'    => now(),
            ]
        );

        $this->command->info('  ✓ Super Admin created: filetrack@mosrac.gov.zw / Ministry@2018');
        $this->command->info('=== Seeding Complete: Super Admin account seeded exclusively. Records Admin & Permanent Secretary accounts to be created via Super Admin. ===');
    }
}
