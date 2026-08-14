<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\NotificationPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * AJAX polling endpoint — returns the 15 most recent notifications
     * and the unread count. Uses a single DB query for both.
     */
    public function poll()
    {
        /** @var User $user */
        $user = Auth::user();
        $latest = $user->notifications()
            ->latest()
            ->limit(15)
            ->get(['id', 'type', 'data', 'read_at', 'created_at']);

        // Count unread from the already-fetched collection first,
        // then fall back to a DB count only if there could be more unread
        // outside the top 15 (rare but correct).
        $unreadInLatest = $latest->whereNull('read_at')->count();
        $unreadCount = $unreadInLatest < $latest->count()
            ? $unreadInLatest   // all unread items are in the top 15
            : $user->notifications()->whereNull('read_at')->count(); // may have more

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $latest
                ->map(fn ($n) => NotificationPresenter::present($n))
                ->values(),
        ]);
    }

    /**
     * Mark specific notification IDs as read (called when dropdown opens or item clicked).
     */
    public function markVisibleAsRead(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'max:15'],
            'ids.*' => ['required', 'uuid'],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->notifications()
            ->whereIn('id', $data['ids'])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => $user->notifications()->whereNull('read_at')->count(),
        ]);
    }
}
