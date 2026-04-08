<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'job_id',
        'direct_request_id',
        'client_id',
        'client_phone',
        'professional_id',
        'professional_phone',
        'agreed_price',
        'status',
    ];

    public function directRequest()
    {
        return $this->belongsTo(DirectRequest::class);
    }

    public function job()
    {
        return $this->belongsTo(JobPost::class, 'job_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function professional()
    {
        return $this->belongsTo(User::class, 'professional_id');
    }
}
