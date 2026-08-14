<?php

namespace App\Services;

use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    // Cache TTL in seconds (5 minutes)
    private const TTL = 300;

    private static function hasDeptOwnershipColumns(): bool
    {
        static $hasColumns = null;

        if ($hasColumns === null) {
            $hasColumns = Schema::hasColumn('file_records', 'current_department_id')
                && Schema::hasColumn('file_records', 'current_user_id');
        }

        return $hasColumns;
    }

    /* ──────────────────────────────────────────────────────────────
     *  SUPER ADMIN — system-wide cached stats
     * ──────────────────────────────────────────────────────────── */
    public function superAdminStats(): array
    {
        return Cache::remember('sa_stats', self::TTL, fn () => [
            'total_files'       => FileRecord::count(),
            'total_departments' => Department::count(),
            'total_users'       => User::count(),
            'total_transfers'   => FileTransfer::count(),
            'total_admins'      => User::where('role', 'admin')->count(),
        ]);
    }

    public function superAdminMovementStats(): array
    {
        return Cache::remember('sa_movement_stats', self::TTL, function () {
            $counts = FileMovement::query()
                ->selectRaw('action, COUNT(*) as aggregate')
                ->whereIn('action', ['created', 'transferred'])
                ->groupBy('action')
                ->pluck('aggregate', 'action');

            return [
                'created' => (int) ($counts['created'] ?? 0),
                'transferred' => (int) ($counts['transferred'] ?? 0),
            ];
        });
    }

    public function departmentFileCounts(): object
    {
        return Cache::remember(
            'dept_file_counts',
            self::TTL,
            fn () => Department::withCount('files')->orderByDesc('files_count')->get()
        );
    }

    public function superAdminRecentData(): array
    {
        return [
            'recentTransfers' => FileTransfer::with(['sender', 'receiver', 'file.department'])
                ->latest()->take(10)->get(),
            'recentMovements' => FileMovement::with(['file', 'fromUser', 'toUser', 'fromDept', 'toDept'])
                ->latest()->take(10)->get(),
        ];
    }

    /* ──────────────────────────────────────────────────────────────
     *  ADMIN — department-scoped cached stats
     * ──────────────────────────────────────────────────────────── */
    public function adminStats(int $deptId): array
    {
        return Cache::remember("admin_stats_{$deptId}", self::TTL, function () use ($deptId) {
            // current_department_id and current_user_id are added by the
            // 2026_07_29 department-ownership migration. Guard against the
            // columns not yet existing so the dashboard never throws a 500.
            $hasDeptOwnershipColumns = self::hasDeptOwnershipColumns();

            $deptFiles = $hasDeptOwnershipColumns
                ? FileRecord::where('current_department_id', $deptId)->count()
                : FileRecord::where('department_id', $deptId)->count();

            $pendingAssignments = $hasDeptOwnershipColumns
                ? FileRecord::where('current_department_id', $deptId)
                    ->whereNull('current_user_id')
                    ->where('status', 'pending_assignment')
                    ->count()
                : 0;

            return [
                'dept_files'          => $deptFiles,
                'dept_users'          => User::where('department_id', $deptId)->count(),
                'total_transfers'     => FileMovement::where(function ($q) use ($deptId) {
                    $q->where('from_department', $deptId)
                      ->orWhere('to_department',  $deptId);
                })->where('action', 'transferred')->count(),
                'pending_assignments' => $pendingAssignments,
            ];
        });
    }

    public function adminRecentData(int $deptId): array
    {
        $hasDeptOwnershipColumns = self::hasDeptOwnershipColumns();

        $recentFiles = $hasDeptOwnershipColumns
            ? FileRecord::with(['currentHolder'])
                ->where('current_department_id', $deptId)->latest()->take(7)->get()
            : FileRecord::with(['currentHolder'])
                ->where('department_id', $deptId)->latest()->take(7)->get();

        $pendingFiles = $hasDeptOwnershipColumns
            ? FileRecord::with(['creator'])
                ->where('current_department_id', $deptId)
                ->whereNull('current_user_id')
                ->where('status', 'pending_assignment')
                ->latest()->take(5)->get()
            : collect();

        return [
            'recentFiles'    => $recentFiles,
            'recentActivity' => FileMovement::with(['file', 'fromUser', 'toUser', 'fromDept', 'toDept'])
                ->where(fn ($q) => $q->where('from_department', $deptId)->orWhere('to_department', $deptId))
                ->latest()->take(8)->get(),
            'recentUsers'    => User::with('designation')
                ->where('department_id', $deptId)->latest()->take(5)->get(),
            'pendingFiles'   => $pendingFiles,
        ];
    }

    /* ──────────────────────────────────────────────────────────────
     *  USER — personal cached stats
     * ──────────────────────────────────────────────────────────── */
    public function userStats(int $userId): array
    {
        return Cache::remember("user_stats_{$userId}", self::TTL, fn () => [
            'total_my_files' => FileRecord::where(fn ($q) => $q
                ->where('created_by',        $userId)
                ->orWhere('current_user_id', $userId)
            )->count(),
            'sent_files'     => FileMovement::where('from_user', $userId)
                ->where('action', 'transferred')->count(),
            'received_files' => FileMovement::where('to_user', $userId)
                ->where('action', 'transferred')->count(),
        ]);
    }

    /* ──────────────────────────────────────────────────────────────
     *  CACHE INVALIDATION — call after writes
     * ──────────────────────────────────────────────────────────── */
    public static function clearSuperAdminCache(): void
    {
        Cache::forget('sa_stats');
        Cache::forget('sa_movement_stats');
        Cache::forget('dept_file_counts');
    }

    public static function clearAdminCache(int $deptId): void
    {
        Cache::forget("admin_stats_{$deptId}");
    }

    public static function clearUserCache(int $userId): void
    {
        Cache::forget("user_stats_{$userId}");
    }
}
