<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $unreadCount = $user->unreadNotifications()->count();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Mark unread notifications as read when visited
        $user->unreadNotifications->markAsRead();

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}
