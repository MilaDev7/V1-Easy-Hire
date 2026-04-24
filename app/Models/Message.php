<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'message',
        'email_requested',
        'email_sent_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'email_requested' => 'boolean',
            'email_sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
