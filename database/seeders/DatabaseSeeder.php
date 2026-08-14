<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Main seeder.
 *
 * Default (production-safe):
 *   php artisan db:seed
 *   → creates Super Admin only
 *
 * Full sample data (for local dev / testing verification):
 *   php artisan db:seed --class=DatabaseSeeder
 *   with SEED_SAMPLE_DATA=true in .env
 *
 * OR use the --sample flag shortcut:
 *   php artisan migrate:fresh --seed
 *   (SEED_SAMPLE_DATA defaults to true when APP_ENV=local or testing)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== FileTrack System Seeder ===');

        // ── 1. Super Admin (always created) ───────────────────────
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name'                => 'Super Admin',
                'password'            => Hash::make('Password@123'),
                'role'                => 'super_admin',
                'department_id'       => null,
                'designation_id'      => null,
                'is_active'           => true,
                'can_create_file'     => false,
                'must_change_password'=> false,
                'email_verified_at'   => now(),
            ]
        );

        $this->command->line('  ✓ Super Admin created');

        // ── 2. Sample data (local / testing environments) ──────────
        // Seeded automatically when APP_ENV is local or testing,
        // or when SEED_SAMPLE_DATA=true is set in .env.
        $seedSample = filter_var(
            env('SEED_SAMPLE_DATA', in_array(app()->environment(), ['local', 'testing'], true)),
            FILTER_VALIDATE_BOOLEAN
        );

        // if ($seedSample) {
        //     $this->command->info('');
        //     $this->command->info('Seeding sample data...');

        //     $this->call([
        //         // DepartmentSeeder::class,
        //         // DesignationSeeder::class,
        //         // SampleUsersSeeder::class,
        //         // FileRecordSeeder::class,
        //     ]);
        // }

        $this->command->info('');
        $this->command->info('=== Seeding complete ===');
        $this->command->line('  Super Admin: superadmin@example.com / Password@123');

        // if ($seedSample) {
        //     $this->command->line('  Admin:  admin@filetrack.local  / Admin@1234');
        //     $this->command->line('  User1:  user1@filetrack.local  / User@1234  (Administration dept)');
        //     $this->command->line('  User2:  user2@filetrack.local  / User@1234  (IT dept)');
        //     $this->command->line('');
        //     $this->command->line('  Sample files seeded (department-scoped uniqueness demo):');
        //     $this->command->line('  FILE-1001 in Administration   ← same number...');
        //     $this->command->line('  FILE-1001 in Finance          ← ...different dept, both valid');
        //     $this->command->line('  FILE-2001 in Admin (→ IT)     ← transferred, no conflict');
        //     $this->command->line('  FILE-2001 in IT (native)      ← IT-origin, coexists');
        // }
    }
}
