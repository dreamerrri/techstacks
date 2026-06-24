<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::forCurrentUser()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

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

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::forCurrentUser()->unread()->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function generateHrAdminNotifications()
    {
        if (!auth()->user() || (auth()->user()->role !== 'admin' && auth()->user()->role !== 'hr')) {
            abort(403);
        }

        NotificationService::generateHrAdminNotifications();

        return response()->json(['success' => true, 'message' => 'Notifications generated successfully']);
    }
}
