<?php

namespace App\Console\Commands;

use App\Services\ApplyCreditService;
use Illuminate\Console\Command;

class ResetProfessionalApplyLimits extends Command
{
    protected $signature = 'professional-applies:reset-monthly';

    protected $description = 'Reset professional monthly apply limits when the monthly period has elapsed';

    public function __construct(private ApplyCreditService $applyCreditService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $updated = $this->applyCreditService->resetExpiredWallets();
        $this->info("Professional apply wallets reset: {$updated}");

        return self::SUCCESS;
    }
}
