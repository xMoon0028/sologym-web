<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Scan for scheduled payroll releases that have matured
        try {
            app(NotificationService::class)->checkAndDispatchScheduledReleases();
        } catch (\Throwable $e) {
            // Non-blocking catch
        }

        $unreadCount = $user->unreadNotificationsCount();

        if ($request->wantsJson()) {
            $notifications = $user->appNotifications()->take(20)->get();
            return response()->json([
                'success' => true,
                'unreadCount' => $unreadCount,
                'notifications' => $notifications,
            ]);
        }

        $filter = $request->query('filter', 'all');
        $query = $user->appNotifications();

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'payslips') {
            $query->where('type', 'payslip_released');
        } elseif ($filter === 'leaves') {
            $query->where('type', 'like', 'leave_%');
        } elseif ($filter === 'time_credits') {
            $query->where('type', 'like', 'time_credit_%');
        } elseif ($filter === 'attendance') {
            $query->where('type', 'like', 'attendance_%');
        }

        $notifications = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => $user->appNotifications()->count(),
            'unread' => $unreadCount,
            'payslips' => $user->appNotifications()->where('type', 'payslip_released')->count(),
            'leaves' => $user->appNotifications()->where('type', 'like', 'leave_%')->count(),
            'time_credits' => $user->appNotifications()->where('type', 'like', 'time_credit_%')->count(),
            'attendance' => $user->appNotifications()->where('type', 'like', 'attendance_%')->count(),
        ];

        return view('notifications.index', compact('notifications', 'unreadCount', 'filter', 'counts'));
    }

    public function markAsRead(AppNotification $notification)
    {
        if ($notification->user_id === auth()->id()) {
            $notification->update(['is_read' => true]);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        auth()->user()->appNotifications()->where('is_read', false)->update(['is_read' => true]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
