<?php

namespace App\Http\Controllers;

use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Cached KPIs
        $stats = $this->dashboard->userStats($userId);

        // Files the user currently holds or created
        $myFiles = FileRecord::with(['department', 'currentHolder'])
            ->where(fn ($q) => $q->where('created_by', $userId)->orWhere('current_user_id', $userId))
            ->latest()->take(10)->get();

        // Files received by this user
        $receivedFiles = FileTransfer::with(['file.department', 'sender'])
            ->where('receiver_id', $userId)
            ->latest()->take(8)->get();

        // Files sent (transferred away) by this user
        $sentFiles = FileTransfer::with(['file.department', 'receiver'])
            ->where('sender_id', $userId)
            ->latest()->take(8)->get();

        $recentActivity = FileMovement::with(['file', 'fromUser', 'toUser', 'fromDept', 'toDept'])
            ->where(fn ($q) => $q->where('from_user', $userId)->orWhere('to_user', $userId))
            ->latest()->take(8)->get();

        $unreadNotifications = $user->notifications()
            ->whereNull('read_at')
            ->latest()
            ->limit(5)
            ->get();

        return view('user.dashboard', [
            'myFiles' => $myFiles,
            'receivedFiles' => $receivedFiles,
            'sentFiles' => $sentFiles,
            'totalMyFiles' => $stats['total_my_files'],
            'totalSentFiles' => $stats['sent_files'],
            'totalReceivedFiles' => $stats['received_files'],
            'recentActivity' => $recentActivity,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }
}
