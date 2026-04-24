<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'description',
        'budget',
        'location',
        'skill',
        'status',
        'start_date',
        'deadline',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // app/Models/Application.php
    public function applications()
    {
        // Add 'job_id' because it doesn't match 'job_post_id'
        return $this->hasMany(Application::class, 'job_id');
    }

    public static function autoExpireOpenJobs(): void
    {
        static::query()
            ->where('status', 'open')
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', now()->toDateString())
            ->update(['status' => 'expired']);
    }
}
