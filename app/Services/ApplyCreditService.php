<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\ProfessionalApplyWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApplyCreditService
{
    /**
     * Prefix used to mark professional-withdrawn applications as non-refundable.
     */
    public const WITHDRAWN_TAG = '[WITHDRAWN_NO_REFUND]';

    /**
     * Default monthly apply limit for free professionals.
     */
    public const FREE_MONTHLY_LIMIT = 0;

    public function walletState(int $professionalId): array
    {
        $wallet = $this->getOrCreateLockedWallet($professionalId);

        return $this->stateFromWallet($wallet);
    }

    public function canApply(int $professionalId): bool
    {
        $state = $this->walletState($professionalId);

        return ($state['remaining_total'] ?? 0) > 0;
    }

    public function consumeApply(int $professionalId): array
    {
        return DB::transaction(function () use ($professionalId) {
            $wallet = $this->getOrCreateLockedWallet($professionalId);
            $this->expireWalletIfNeeded($wallet, now());

            if ((int) $wallet->remaining_applies <= 0) {
                return [
                    'success' => false,
                    'message' => 'Apply limit reached',
                    'state' => $this->stateFromWallet($wallet),
                ];
            }

            $wallet->remaining_applies = (int) $wallet->remaining_applies - 1;
            $wallet->save();

            return [
                'success' => true,
                'state' => $this->stateFromWallet($wallet->fresh('currentPlan')),
            ];
        });
    }

    public function activateMonthlyPlan(int $professionalId, Plan $plan): array
    {
        $planLimit = max((int) $plan->apply_limit_monthly, 0);
        $durationDays = max((int) ($plan->duration_days ?? 30), 1);

        return DB::transaction(function () use ($professionalId, $plan, $planLimit, $durationDays) {
            $wallet = $this->getOrCreateLockedWallet($professionalId);

            $wallet->current_plan_id = $plan->id;
            $wallet->monthly_limit = $planLimit;
            $wallet->remaining_applies = $planLimit;
            $wallet->expiry_date = now()->addDays($durationDays)->endOfDay();
            $wallet->save();

            return $this->stateFromWallet($wallet->fresh('currentPlan'));
        });
    }

    public function addExtraApplies(int $professionalId, int $quantity): array
    {
        $safeQuantity = max($quantity, 0);

        return DB::transaction(function () use ($professionalId, $safeQuantity) {
            $wallet = $this->getOrCreateLockedWallet($professionalId);
            $now = now();
            $this->expireWalletIfNeeded($wallet, $now);

            if (! $wallet->expiry_date || $now->greaterThan($wallet->expiry_date)) {
                $wallet->expiry_date = $now->copy()->addDays(30)->endOfDay();
            }

            $wallet->remaining_applies = max((int) $wallet->remaining_applies, 0) + $safeQuantity;
            $wallet->save();

            return $this->stateFromWallet($wallet->fresh('currentPlan'));
        });
    }

    public function resetExpiredWallets(): int
    {
        $now = now();

        return ProfessionalApplyWallet::query()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $now)
            ->where('remaining_applies', '>', 0)
            ->update(['remaining_applies' => 0]);
    }

    private function getOrCreateLockedWallet(int $professionalId): ProfessionalApplyWallet
    {
        return DB::transaction(function () use ($professionalId) {
            $wallet = ProfessionalApplyWallet::where('user_id', $professionalId)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                $wallet = ProfessionalApplyWallet::create([
                    'user_id' => $professionalId,
                    'monthly_limit' => self::FREE_MONTHLY_LIMIT,
                    'remaining_applies' => self::FREE_MONTHLY_LIMIT,
                    'expiry_date' => null,
                ]);

                return $wallet->fresh('currentPlan');
            }

            if ((int) $wallet->remaining_applies < 0) {
                $wallet->remaining_applies = 0;
                $wallet->save();
            }

            // Legacy wallets may have applies without expiry; normalize once.
            if ((int) $wallet->remaining_applies > 0 && ! $wallet->expiry_date) {
                $wallet->expiry_date = now()->addDays(30)->endOfDay();
                $wallet->save();
            }

            $this->expireWalletIfNeeded($wallet, now());

            return $wallet->fresh('currentPlan');
        });
    }

    private function expireWalletIfNeeded(ProfessionalApplyWallet $wallet, Carbon $now): void
    {
        if (! $wallet->expiry_date) {
            return;
        }

        if ($now->greaterThan($wallet->expiry_date) && (int) $wallet->remaining_applies > 0) {
            $wallet->remaining_applies = 0;
            $wallet->save();
        }
    }

    private function stateFromWallet(ProfessionalApplyWallet $wallet): array
    {
        $remainingApplies = max((int) ($wallet->remaining_applies ?? 0), 0);
        $expiryDate = $wallet->expiry_date ? $wallet->expiry_date->toDateString() : null;
        $daysLeft = 0;

        if ($wallet->expiry_date && now()->lessThanOrEqualTo($wallet->expiry_date)) {
            $daysLeft = max(now()->startOfDay()->diffInDays($wallet->expiry_date->copy()->startOfDay(), false), 0);
        }

        return [
            'monthly_limit' => (int) $wallet->monthly_limit,
            'remaining_applies' => $remainingApplies,
            'monthly_remaining' => 0,
            'extra_remaining' => 0,
            'remaining_total' => $remainingApplies,
            'period_start' => null,
            'period_end' => $expiryDate,
            'expiry_date' => $expiryDate,
            'days_left' => $daysLeft,
            'current_plan_id' => $wallet->current_plan_id,
            'current_plan_name' => $wallet->currentPlan?->name,
            'current_plan_duration_days' => $wallet->currentPlan?->duration_days ? (int) $wallet->currentPlan->duration_days : null,
        ];
    }
}
