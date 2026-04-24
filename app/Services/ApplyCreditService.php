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
    public const FREE_MONTHLY_LIMIT = 5;

    public function walletState(int $professionalId): array
    {
        $wallet = $this->resetAndGetLockedWallet($professionalId);

        return [
            'monthly_limit' => (int) $wallet->monthly_limit,
            'monthly_remaining' => (int) $wallet->monthly_remaining,
            'extra_remaining' => (int) $wallet->extra_remaining,
            'remaining_total' => (int) $wallet->monthly_remaining + (int) $wallet->extra_remaining,
            'period_start' => optional($wallet->period_start)->toDateString(),
            'period_end' => optional($wallet->period_end)->toDateString(),
            'current_plan_id' => $wallet->current_plan_id,
            'current_plan_name' => $wallet->currentPlan?->name,
        ];
    }

    public function canApply(int $professionalId): bool
    {
        $state = $this->walletState($professionalId);

        return ($state['remaining_total'] ?? 0) > 0;
    }

    public function consumeApply(int $professionalId): array
    {
        return DB::transaction(function () use ($professionalId) {
            $wallet = $this->resetAndGetLockedWallet($professionalId);

            if ((int) $wallet->monthly_remaining > 0) {
                $wallet->monthly_remaining = (int) $wallet->monthly_remaining - 1;
            } elseif ((int) $wallet->extra_remaining > 0) {
                $wallet->extra_remaining = (int) $wallet->extra_remaining - 1;
            } else {
                return [
                    'success' => false,
                    'message' => 'Apply limit reached',
                    'state' => $this->stateFromWallet($wallet),
                ];
            }

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

        return DB::transaction(function () use ($professionalId, $plan, $planLimit) {
            $wallet = $this->resetAndGetLockedWallet($professionalId);

            $oldLimit = max((int) $wallet->monthly_limit, 0);
            $oldRemaining = max((int) $wallet->monthly_remaining, 0);
            $alreadyUsed = max($oldLimit - $oldRemaining, 0);

            $wallet->current_plan_id = $plan->id;
            $wallet->monthly_limit = $planLimit;
            $wallet->monthly_remaining = max($planLimit - $alreadyUsed, 0);
            $wallet->save();

            return $this->stateFromWallet($wallet->fresh('currentPlan'));
        });
    }

    public function addExtraApplies(int $professionalId, int $quantity): array
    {
        $safeQuantity = max($quantity, 0);

        return DB::transaction(function () use ($professionalId, $safeQuantity) {
            $wallet = $this->resetAndGetLockedWallet($professionalId);
            $wallet->extra_remaining = (int) $wallet->extra_remaining + $safeQuantity;
            $wallet->save();

            return $this->stateFromWallet($wallet->fresh('currentPlan'));
        });
    }

    public function resetExpiredWallets(): int
    {
        $now = now();
        $updated = 0;

        ProfessionalApplyWallet::where('period_end', '<', $now)
            ->orderBy('id')
            ->chunkById(200, function ($wallets) use (&$updated) {
                foreach ($wallets as $wallet) {
                    $changed = DB::transaction(function () use ($wallet) {
                        $locked = ProfessionalApplyWallet::where('id', $wallet->id)->lockForUpdate()->first();

                        if (! $locked) {
                            return false;
                        }

                        return $this->resetPeriodIfNeeded($locked, now());
                    });

                    if ($changed) {
                        $updated++;
                    }
                }
            });

        return $updated;
    }

    private function resetAndGetLockedWallet(int $professionalId): ProfessionalApplyWallet
    {
        return DB::transaction(function () use ($professionalId) {
            $wallet = ProfessionalApplyWallet::where('user_id', $professionalId)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                [$periodStart, $periodEnd] = $this->monthlyWindow(now());

                $wallet = ProfessionalApplyWallet::create([
                    'user_id' => $professionalId,
                    'monthly_limit' => self::FREE_MONTHLY_LIMIT,
                    'monthly_remaining' => self::FREE_MONTHLY_LIMIT,
                    'extra_remaining' => 0,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'last_reset_at' => now(),
                ]);

                return $wallet->fresh('currentPlan');
            }

            $this->resetPeriodIfNeeded($wallet, now());

            return $wallet->fresh('currentPlan');
        });
    }

    private function resetPeriodIfNeeded(ProfessionalApplyWallet $wallet, Carbon $now): bool
    {
        $periodEnd = $wallet->period_end;

        if (! $periodEnd || $now->greaterThan($periodEnd)) {
            [$periodStart, $newPeriodEnd] = $this->monthlyWindow($now);
            $wallet->period_start = $periodStart;
            $wallet->period_end = $newPeriodEnd;
            $wallet->monthly_remaining = (int) $wallet->monthly_limit;
            $wallet->last_reset_at = $now;
            $wallet->save();

            return true;
        }

        return false;
    }

    private function monthlyWindow(Carbon $now): array
    {
        $start = $now->copy()->startOfMonth()->startOfDay();
        $end = $now->copy()->endOfMonth()->endOfDay();

        return [$start, $end];
    }

    private function stateFromWallet(ProfessionalApplyWallet $wallet): array
    {
        return [
            'monthly_limit' => (int) $wallet->monthly_limit,
            'monthly_remaining' => (int) $wallet->monthly_remaining,
            'extra_remaining' => (int) $wallet->extra_remaining,
            'remaining_total' => (int) $wallet->monthly_remaining + (int) $wallet->extra_remaining,
            'period_start' => optional($wallet->period_start)->toDateString(),
            'period_end' => optional($wallet->period_end)->toDateString(),
            'current_plan_id' => $wallet->current_plan_id,
            'current_plan_name' => $wallet->currentPlan?->name,
        ];
    }
}
