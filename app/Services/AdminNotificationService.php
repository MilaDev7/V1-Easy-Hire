<?php

namespace App\Services;

use App\Models\AdminNotification;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    public function send(string $type, string $message, ?string $link = null): void
    {
        try {
            AdminNotification::create([
                'type' => $type,
                'message' => $message,
                'link' => $link,
                'is_read' => false,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to create admin notification: '.$exception->getMessage());
        }
    }
}

