<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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

    public static function autoCompleteExpiredPendingCompletions(int $days = 3): void
    {
        $cutoff = Carbon::now()->subDays($days);

        static::where('status', 'pending_completion')
            ->where('updated_at', '<=', $cutoff)
            ->with('job')
            ->chunkById(100, function ($contracts) {
                foreach ($contracts as $contract) {
                    $contract->status = 'completed';
                    $contract->save();

                    if ($contract->job && $contract->job->status !== 'completed') {
                        $contract->job->status = 'completed';
                        $contract->job->save();
                    }
                }
            });
    }
}
