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
        $myFiles = FileRecord::with(['department', 'currentHolder', 'folder'])
            ->where(fn ($q) => $q->where('created_by', $userId)->orWhere('current_user_id', $userId))
            ->prioritizeUrgentAndRecent()->take(10)->get();

        // Files received by this user
        $receivedFiles = FileTransfer::with(['file.department', 'file.folder', 'sender'])
            ->leftJoin('files', 'file_transfers.file_id', '=', 'files.id')
            ->select('file_transfers.*')
            ->where('file_transfers.receiver_id', $userId)
            ->orderByRaw("CASE WHEN files.is_urgent = 1 AND (files.status IS NULL OR files.status NOT IN ('completed', 'archived')) AND files.completed_at IS NULL THEN 0 ELSE 1 END ASC")
            ->orderBy('file_transfers.created_at', 'desc')
            ->take(8)->get();

        // Files sent (transferred away) by this user
        $sentFiles = FileTransfer::with(['file.department', 'file.folder', 'receiver'])
            ->leftJoin('files', 'file_transfers.file_id', '=', 'files.id')
            ->select('file_transfers.*')
            ->where('file_transfers.sender_id', $userId)
            ->orderByRaw("CASE WHEN files.is_urgent = 1 AND (files.status IS NULL OR files.status NOT IN ('completed', 'archived')) AND files.completed_at IS NULL THEN 0 ELSE 1 END ASC")
            ->orderBy('file_transfers.created_at', 'desc')
            ->take(8)->get();

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
