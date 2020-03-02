<?php

namespace OpenDominion\Http\Controllers\Dominion;

use DB;

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
