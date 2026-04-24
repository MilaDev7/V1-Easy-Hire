<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessionalApplyWallet extends Model
{
    protected $fillable = [
        'user_id',
        'current_plan_id',
        'monthly_limit',
        'monthly_remaining',
        'extra_remaining',
        'period_start',
        'period_end',
        'last_reset_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'last_reset_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currentPlan()
    {
        return $this->belongsTo(Plan::class, 'current_plan_id');
    }
}
