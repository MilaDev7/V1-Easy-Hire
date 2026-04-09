<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\Schema;

class ApplyCreditService
{
    /**
     * Prefix used to mark professional-withdrawn applications as non-refundable.
     */
    public const WITHDRAWN_TAG = '[WITHDRAWN_NO_REFUND]';

    /**
     * Maximum application credits for professionals.
     */
    public const MAX_APPLY_CREDITS = 5;

    /**
     * Cached schema check for applications.source.
     */
    private ?bool $hasApplicationSourceColumn = null;

    /**
     * Count consumed apply credits.
     * Consumed = manual-apply applications that are still pending +
     * manual-apply withdrawals marked as non-refundable.
     */
    public function usedApplyCredits(int $professionalId): int
    {
        $pendingQuery = Application::where('professional_id', $professionalId)
            ->where('status', 'pending');

        $withdrawnQuery = Application::where('professional_id', $professionalId)
            ->where('status', 'rejected')
            ->where('cover_letter', 'like', self::WITHDRAWN_TAG.'%');

        if ($this->hasApplicationSourceColumn()) {
            $pendingQuery->where('source', 'apply');
            $withdrawnQuery->where('source', 'apply');
        }

        return $pendingQuery->count() + $withdrawnQuery->count();
    }

    private function hasApplicationSourceColumn(): bool
    {
        if ($this->hasApplicationSourceColumn === null) {
            $this->hasApplicationSourceColumn = Schema::hasColumn('applications', 'source');
        }

        return $this->hasApplicationSourceColumn;
    }
}
