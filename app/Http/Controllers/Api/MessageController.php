<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $messages = Message::query()
            ->where('receiver_id', $user->id)
            ->with(['sender:id,name,email'])
            ->latest()
            ->limit(30)
            ->get();

        $unreadCount = Message::query()
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'data' => $messages,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $message = Message::query()
            ->where('receiver_id', $request->user()->id)
            ->findOrFail($id);

        if (! $message->read_at) {
            $message->read_at = now();
            $message->save();
        }

        return response()->json(['message' => 'Message marked as read']);
    }
}
