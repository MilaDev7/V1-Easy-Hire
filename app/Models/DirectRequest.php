<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectRequest extends Model
{
    protected $fillable = [
        'client_id',
        'professional_id',
        'title',
        'description',
        'budget',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function professional()
    {
        return $this->belongsTo(User::class, 'professional_id');
    }
}
