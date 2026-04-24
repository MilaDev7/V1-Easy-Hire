<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessionalApplyWallet extends Model
{
    protected $fillable = [
        'user_id',
        'current_plan_id',
        'monthly_limit',
        'remaining_applies',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'datetime',
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
