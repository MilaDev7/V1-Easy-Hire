<?php

namespace App\Services;

use App\Models\UserNotification;

class NotificationService
{
    public function send(int $userId, string $type, string $title, string $message, ?string $actionUrl = null, array $meta = []): void
    {
        UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'meta' => $meta,
        ]);
    }
}
