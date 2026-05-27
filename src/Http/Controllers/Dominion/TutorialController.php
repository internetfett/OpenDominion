<?php

namespace OpenDominion\Http\Controllers\Dominion;

use Illuminate\Http\Request;
use OpenDominion\Services\User\TutorialService;

class TutorialController extends AbstractDominionController
{
    public function postSkip(Request $request, string $step)
    {
        $service = app(TutorialService::class);
        $user = $request->user();

        if (!$service->skip($user, $step)) {
            $request->session()->flash('alert-warning', 'That tutorial step cannot be skipped.');
        }

        return redirect()->back();
    }

    public function postComplete(Request $request, string $step)
    {
        $service = app(TutorialService::class);
        $user = $request->user();

        if (!$service->markComplete($user, $step)) {
            $request->session()->flash('alert-warning', 'That tutorial step cannot be marked complete manually.');
        }

        return redirect()->back();
    }

    public function postBranch(Request $request, string $branch)
    {
        $service = app(TutorialService::class);
        $user = $request->user();

        if (!$service->chooseBranch($user, $branch)) {
            $request->session()->flash('alert-warning', 'Invalid tutorial branch.');
        }

        return redirect()->back();
    }
}
