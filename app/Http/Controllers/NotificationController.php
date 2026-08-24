<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        // Only allow marking own notifications or if HR/Admin
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'hr') {
            if ($notification->user_id !== auth()->id()) {
                abort(403);
            }
        }

        $notification->markAsRead();
        auth()->user()->clearUnreadNotificationsCountCache();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::forCurrentUser()->unread()->update(['is_read' => true]);
        auth()->user()->clearUnreadNotificationsCountCache();

        return response()->json(['success' => true]);
    }
}