<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'job_posts_limit',
        'direct_requests_limit',
        'duration_days',
        'plan_scope',
        'apply_limit_monthly',
        'extra_apply_quantity',
        'is_active',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
