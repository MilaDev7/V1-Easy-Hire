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
     * Consumed = manual-apply applications that are:
     * - pending, or
     * - accepted, or
     * - withdrawn by professional (tagged as non-refundable).
     *
     * Rejected applications are refundable unless they were withdrawn by
     * professional (tagged with WITHDRAWN_TAG).
     */
    public function usedApplyCredits(int $professionalId): int
    {
        $query = Application::where('professional_id', $professionalId)
            ->where(function ($q) {
                $q->whereIn('status', ['pending', 'accepted'])
                    ->orWhere(function ($withdrawn) {
                        $withdrawn->where('status', 'rejected')
                            ->where('cover_letter', 'like', self::WITHDRAWN_TAG.'%');
                    });
            });

        if ($this->hasApplicationSourceColumn()) {
            $query->where('source', 'apply');
        }

        return $query->count();
    }

    private function hasApplicationSourceColumn(): bool
    {
        if ($this->hasApplicationSourceColumn === null) {
            $this->hasApplicationSourceColumn = Schema::hasColumn('applications', 'source');
        }

        return $this->hasApplicationSourceColumn;
    }
}
