<?php

namespace OpenDominion\Services\Dominion;

use Carbon\Carbon;
use OpenDominion\Models\Dominion;

class ProtectionService
{
    /**
     * Returns whether this under protection (has protection_ticks > 0).
     *
     * @param Dominion $dominion
     * @return bool
     */
    public function isUnderProtection(Dominion $dominion): bool
    {
        return $dominion->protection_ticks;
    }
}
