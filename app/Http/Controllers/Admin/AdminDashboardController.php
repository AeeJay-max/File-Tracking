<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index()
    {
        $user = Auth::user();
        $isSuper = $user->role === 'super_admin';

        if ($isSuper) {
            $stats = $this->dashboard->superAdminStats();
            $mvStats = $this->dashboard->superAdminMovementStats();
            $depts = $this->dashboard->departmentFileCounts();
            $recent = $this->dashboard->superAdminRecentData();

            return view('super_admin.dashboard', [
                'totalFiles' => $stats['total_files'],
                'totalDepartments' => $stats['total_departments'],
                'totalUsers' => $stats['total_users'],
                'totalTransfers' => $stats['total_transfers'],
                'totalAdmins' => $stats['total_admins'],
                'movementStats' => $mvStats,
                'departmentFileCounts' => $depts,
                'recentTransfers' => $recent['recentTransfers'],
                'recentMovements' => $recent['recentMovements'],
            ]);
        }

        $deptId = $user->department_id;
        $stats = $this->dashboard->adminStats($deptId);
        $recent = $this->dashboard->adminRecentData($deptId);

        return view('admin.dashboard', [
            'deptFiles' => $stats['dept_files'],
            'deptUsers' => $stats['dept_users'],
            'totalTransfers' => $stats['total_transfers'],
            'pendingAssignments' => $stats['pending_assignments'],
            'recentFiles' => $recent['recentFiles'],
            'recentActivity' => $recent['recentActivity'],
            'recentUsers' => $recent['recentUsers'],
            'pendingFiles' => $recent['pendingFiles'],
        ]);
    }
}
