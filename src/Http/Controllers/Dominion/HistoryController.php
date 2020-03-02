<?php

namespace OpenDominion\Http\Controllers\Dominion;

use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Calculators\Dominion\PopulationCalculator;
use OpenDominion\Calculators\NetworthCalculator;
use OpenDominion\Helpers\NotificationHelper;
use OpenDominion\Services\Dominion\ProtectionService;
use OpenDominion\Services\Dominion\QueueService;


class HistoryController extends AbstractDominionController
{
    public function getHistory()
    {
        $resultsPerPage = 25;
        $selectedDominion = $this->getSelectedDominion();

        $history = DB::table('dominion_history')
                            ->where('dominion_history.dominion_id', '=', $selectedDominion->id)
                            ->orderBy('dominion_history.created_at')
                            ->pluck('dominions', 'alignment')->all();

        return view('pages.dominion.history', [
            'history' => $history
        ]);
    }

}
