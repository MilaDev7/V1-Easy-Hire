<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'professional_id',
        'cover_letter',
        'status',
    ];

    public function job()
    {
        return $this->belongsTo(JobPost::class);
    }

    public function professional()
    {
        return $this->belongsTo(User::class, 'professional_id');
    }
}
