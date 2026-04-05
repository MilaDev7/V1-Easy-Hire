<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'job_posts_limit',
        'duration_days',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
