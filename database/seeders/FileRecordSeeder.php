<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FileRecordSeeder
 *
 * Seeds sample file records that deliberately demonstrate department-scoped
 * file number uniqueness:
 *
 *   • FILE-1001  exists in Administration (Dept A)  ← allowed
 *   • FILE-1001  exists in Finance        (Dept B)  ← allowed — DIFFERENT department
 *   • FILE-1001  CANNOT be created again in Administration — enforced at DB level
 *
 * Also seeds FILE-2001 in Administration and transfers it to IT, proving that
 * a cross-department transfer does not conflict with IT's own FILE-2001.
 */
class FileRecordSeeder extends Seeder
{
    public function run(): void
    {
        $admin   = Department::where('code', 'ADMIN')->first();
        $finance = Department::where('code', 'FIN')->first();
        $it      = Department::where('code', 'IT')->first();
        $hr      = Department::where('code', 'HR')->first();

        if (! $admin || ! $finance || ! $it) {
            $this->command->warn('Required departments missing — run DepartmentSeeder first.');
            return;
        }

        // Creator: the first user found in each department (falls back to any user)
        $adminUser   = User::where('department_id', $admin->id)->where('is_active', true)->first()
                    ?? User::where('is_active', true)->first();
        $financeUser = User::where('department_id', $finance->id)->where('is_active', true)->first()
                    ?? $adminUser;
        $itUser      = User::where('department_id', $it->id)->where('is_active', true)->first()
                    ?? $adminUser;

        if (! $adminUser) {
            $this->command->warn('No active users found — run SampleUsersSeeder first.');
            return;
        }

        // ── Scenario 1: Same file number (FILE-1001) in two different departments ─
        // This is the core requirement: both are valid, neither conflicts.

        $fileA = $this->createFile([
            'file_number'           => 'FILE-1001',
            'file_name'             => 'Administration Budget Requisition 2026',
            'department_id'         => $admin->id,
            'current_department_id' => $admin->id,
            'created_by'            => $adminUser->id,
            'current_user_id'       => $adminUser->id,
            'remarks'               => 'FILE-1001 in Administration — should coexist with Finance FILE-1001.',
            'status'                => 'active',
        ], $adminUser->id, $admin->id);

        $fileB = $this->createFile([
            'file_number'           => 'FILE-1001',
            'file_name'             => 'Finance Procurement Request 2026',
            'department_id'         => $finance->id,
            'current_department_id' => $finance->id,
            'created_by'            => $financeUser->id,
            'current_user_id'       => $financeUser->id,
            'remarks'               => 'FILE-1001 in Finance — same number as Administration, different department.',
            'status'                => 'active',
        ], $financeUser->id, $finance->id);

        // ── Scenario 2: Cross-department transfer with same-number conflict ────────
        // FILE-2001 is created in Administration and transferred to IT.
        // IT also has its own FILE-2001. The transfer must NOT cause a DB conflict
        // because the transferred file keeps its origin department_id (ADMIN),
        // and only current_department_id changes to IT.

        $fileC = $this->createFile([
            'file_number'           => 'FILE-2001',
            'file_name'             => 'IT Infrastructure Proposal (originated in Admin)',
            'department_id'         => $admin->id,     // origin: Administration
            'current_department_id' => $it->id,        // currently held by IT
            'created_by'            => $adminUser->id,
            'current_user_id'       => $itUser->id,
            'remarks'               => 'Originally FILE-2001 in Admin, transferred to IT.',
            'status'                => 'active',
        ], $adminUser->id, $admin->id);

        // IT's own FILE-2001 — different origin, no conflict with transferred fileC
        $fileD = $this->createFile([
            'file_number'           => 'FILE-2001',
            'file_name'             => 'IT Systems Audit Report 2026',
            'department_id'         => $it->id,         // origin: IT
            'current_department_id' => $it->id,
            'created_by'            => $itUser->id,
            'current_user_id'       => $itUser->id,
            'remarks'               => 'IT-native FILE-2001 — coexists with Admin-origin FILE-2001.',
            'status'                => 'active',
        ], $itUser->id, $it->id);

        // ── Scenario 3: Normal unique files per department ────────────────────────
        $this->createFile([
            'file_number'           => 'FILE-3001',
            'file_name'             => 'HR Recruitment Drive 2026',
            'department_id'         => $hr ? $hr->id : $admin->id,
            'current_department_id' => $hr ? $hr->id : $admin->id,
            'created_by'            => $adminUser->id,
            'current_user_id'       => $adminUser->id,
            'remarks'               => 'Standard HR file.',
            'status'                => 'active',
        ], $adminUser->id, $hr ? $hr->id : $admin->id);

        $this->command->info('File records seeded:');
        $this->command->line('  FILE-1001 in Administration  (ID: '.($fileA?->id ?? '—').')');
        $this->command->line('  FILE-1001 in Finance         (ID: '.($fileB?->id ?? '—').') ← same number, different dept');
        $this->command->line('  FILE-2001 in Admin→IT        (ID: '.($fileC?->id ?? '—').') ← transferred, no conflict');
        $this->command->line('  FILE-2001 in IT (native)     (ID: '.($fileD?->id ?? '—').') ← IT-origin, coexists');
        $this->command->info('Total file records: '.FileRecord::count());
    }

    /**
     * Create a file record if it does not already exist (idempotent).
     * Uses (department_id, file_number) as the identity key — the composite unique.
     */
    private function createFile(array $attributes, int $creatorId, int $deptId): ?FileRecord
    {
        $existing = FileRecord::where('department_id', $attributes['department_id'])
            ->where('file_number', $attributes['file_number'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $file = null;

        DB::transaction(function () use ($attributes, $creatorId, $deptId, &$file) {
            $file = FileRecord::create($attributes);

            // Record the creation movement
            FileMovement::create([
                'file_id'         => $file->id,
                'from_user'       => $creatorId,
                'to_user'         => $creatorId,
                'from_department' => $deptId,
                'to_department'   => $deptId,
                'action'          => 'created',
                'remarks'         => 'File created by seeder: '.$file->file_number,
            ]);
        });

        return $file;
    }
}
