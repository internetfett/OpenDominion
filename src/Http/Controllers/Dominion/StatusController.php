<?php

namespace OpenDominion\Http\Controllers\Dominion;

use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Calculators\Dominion\PopulationCalculator;
use OpenDominion\Calculators\NetworthCalculator;
use OpenDominion\Helpers\NotificationHelper;
use OpenDominion\Services\Dominion\ProtectionService;
use OpenDominion\Services\Dominion\QueueService;

# ODA
use OpenDominion\Helpers\UnitHelper;

class StatusController extends AbstractDominionController
{
    public function getStatus()
    {
        $resultsPerPage = 25;
        $selectedDominion = $this->getSelectedDominion();

        $notifications = $selectedDominion->notifications()->paginate($resultsPerPage);

        return view('pages.dominion.status', [
            'dominionProtectionService' => app(ProtectionService::class),
            'landCalculator' => app(LandCalculator::class),
            'militaryCalculator' => app(MilitaryCalculator::class),
            'networthCalculator' => app(NetworthCalculator::class),
            'notificationHelper' => app(NotificationHelper::class),
            'populationCalculator' => app(PopulationCalculator::class),
            'queueService' => app(QueueService::class),
            'unitHelper' => app(UnitHelper::class),
            'notifications' => $notifications
        ]);
    }

    public function postStatus(TickActionRequest $request)
    {
        $dominion = $this->getSelectedDominion();
        $tickActionService = app(TickActionService::class);

        try {
            $result = $tickActionService->tickDominion($dominion);

        } catch (GameException $e) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors([$e->getMessage()]);
        }

    }

}
