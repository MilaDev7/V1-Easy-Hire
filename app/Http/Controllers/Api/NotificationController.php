<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = UserNotification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(30)
            ->get()
            ->map(function (UserNotification $notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'action_url' => $notification->action_url,
                    'meta' => $notification->meta,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            });

        $unreadCount = UserNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'data' => $notifications,
            'unread_count' => (int) $unreadCount,
        ]);
    }

    public function unreadCount()
    {
        $count = UserNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => (int) $count]);
    }

    public function markRead($id)
    {
        $notification = UserNotification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (! $notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllRead()
    {
        UserNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
