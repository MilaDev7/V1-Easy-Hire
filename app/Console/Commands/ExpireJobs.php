<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\JobPost;
use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireJobs extends Command
{
    protected $signature = 'jobs:expire';

    protected $description = 'Expire jobs and applications with no response';

    public function handle()
    {
        $clientRefunded = 0;
        $proRefunded = 0;

        // 1. Jobs with deadline passed and no applications - expire and refund client
        $expiredJobs = JobPost::where('status', 'open')
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->whereDoesntHave('applications')
            ->get();

        foreach ($expiredJobs as $job) {
            $subscription = Subscription::where('user_id', $job->client_id)
                ->where('status', 'active')
                ->first();

            if ($subscription) {
                $subscription->increment('remaining_posts');
                $clientRefunded++;
            }

            $job->update(['status' => 'expired']);
        }

        $this->info("Expired {$expiredJobs->count()} jobs. Refunded {$clientRefunded} posts to clients.");

        // 2. Jobs with deadline passed with applications but no accepted - expire and refund client
        $jobsWithNoPendingApps = JobPost::where('status', 'open')
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->get()
            ->filter(function ($job) {
                return $job->applications->where('status', 'pending')->isEmpty();
            });

        $expiredNoPending = 0;
        foreach ($jobsWithNoPendingApps as $job) {
            $hasAccepted = $job->applications->where('status', 'accepted')->isNotEmpty();

            if (! $hasAccepted) {
                $subscription = Subscription::where('user_id', $job->client_id)
                    ->where('status', 'active')
                    ->first();

                if ($subscription) {
                    $subscription->increment('remaining_posts');
                    $clientRefunded++;
                }

                $job->update(['status' => 'expired']);
                $expiredNoPending++;
            }
        }

        if ($expiredNoPending > 0) {
            $this->info("Expired {$expiredNoPending} more jobs with no pending applications.");
        }

        // 3. Auto-reject pending applications that have been waiting too long (30+ days)
        $staleApplications = Application::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(30))
            ->get();

        foreach ($staleApplications as $application) {
            $application->update(['status' => 'rejected']);
            $proRefunded++;
        }

        if ($proRefunded > 0) {
            $this->info("Auto-rejected {$proRefunded} stale applications. Professionals can apply for more jobs.");
        }

        return Command::SUCCESS;
    }
}
