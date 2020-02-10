<?php

namespace OpenDominion\Services\Dominion\Actions;

use DB;
use OpenDominion\Exceptions\GameException;
use OpenDominion\Models\Dominion;
use OpenDominion\Services\Dominion\ProtectionService;
use OpenDominion\Services\Dominion\TickService;
use OpenDominion\Services\NotificationService;

class InvadeActionService
{

    /** @var ProtectionService */
    protected $protectionService;

    /**
     * TickActionService constructor.
     *
     * @param ProtectionService $protectionService
     */
    public function __construct(
        ProtectionService $protectionService
    ) {
        $this->protectionService = $protectionService;
    }

    /**
     * Invades dominion $target from $dominion.
     *
     * @param Dominion $dominion
     * @param Dominion $target
     * @param array $units
     * @return array
     * @throws GameException
     */
    public function tickDominion(Dominion $dominion): array
    {
        $this->guardLockedDominion($dominion);
        $this->guardLockedDominion($target);

        DB::transaction(function () use ($dominion) {
            // Checks
            if($dominion->user_id !== Auth::user()->id)
            {
                throw new GameException('You cannot tick for other dominions than your own.');
            }

            if($dominion->protection_ticks <= 0)
            {
                throw new GameException('You do not have any protection ticks left.');
            }

        });

        # Run the tick.
        $this->tickService->tickHourly($dominion);

        $this->notificationService->sendNotifications($dominion, 'irregular_dominion');
        return [
            'message' => 'One tick has been processed. You now have ' . $dominion->protection_ticks . ' left.',
            'alert-type' => 'success',
            'redirect' => route('dominion.status')
        ];
    }
}
