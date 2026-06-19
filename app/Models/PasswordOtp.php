<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'used',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used' => 'boolean',
        ];
    }

    public function scopeValidFor($query, string $email)
    {
        return $query
            ->where('email', $email)
            ->where('used', false)
            ->where('expires_at', '>', now());
    }
}
