<?php

namespace OpenDominion\Services\Dominion;

use Carbon\Carbon;
use OpenDominion\Models\Dominion;

class ProtectionService
{
    # Protection in ODA is 6 hours.
    # 6 hours = 24 ticks
    public const PROTECTION_DURATION_IN_HOURS = 7; // Changed 7 for Arena

    /**
     * Returns whether this under protection (has protection_ticks > 0).
     *
     * @param Dominion $dominion
     * @return bool
     */
    public function isUnderProtection(Dominion $dominion): bool
    {
        if($dominion->protection_ticks > 0)
        {
          return true;
        }
        else
        {
          return false;
        }
    }
}
