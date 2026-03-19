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
        'city',
        'cv',
        'certificate',
        'average_rating',
        'total_reviews'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}