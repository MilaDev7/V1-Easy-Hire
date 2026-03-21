<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
protected $fillable = [
    'user_id',
    'skill',
    'experience',
    'bio',
    'age',
    'gender',
    'location', // <--- Change this from 'city' to 'location'
    'cv',
    'certificate',
    'status'
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}