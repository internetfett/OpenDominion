<?php

namespace OpenDominion\Http\Controllers;

use Auth;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Round;
use OpenDominion\Services\Dominion\SelectorService;

class TermsAndConditionsController extends AbstractController
{
    public function getIndex()
    {
        return view('pages.termsandconditions', [
            'company_name' => 'ODArena',
            'company_address' => 'Cyprus',
        ]);
    }
}
