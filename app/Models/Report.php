<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'contract_id',
        'reporter_id',
        'reported_id',
        'reason',
        'status',
        'action_taken',
        'resolved_at',
    ];

    public function contract()
    {
        return $this->belongsTo(\App\Models\Contract::class);
    }

    public function reporter()
    {
        return $this->belongsTo(\App\Models\User::class, 'reporter_id');
    }

    public function reported()
    {
        return $this->belongsTo(\App\Models\User::class, 'reported_id');
    }
}
