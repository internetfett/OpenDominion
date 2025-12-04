<?php

namespace OpenDominion\Http\Controllers\Dominion;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenDominion\Calculators\Dominion\EspionageCalculator;
use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Calculators\Dominion\RangeCalculator;
use OpenDominion\Exceptions\GameException;
use OpenDominion\Helpers\EspionageHelper;
use OpenDominion\Helpers\ValuablesHelper;
use OpenDominion\Http\Requests\Dominion\Actions\PerformEspionageRequest;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Valuable;
use OpenDominion\Services\Dominion\Actions\EspionageActionService;
use OpenDominion\Services\Dominion\GovernmentService;
use OpenDominion\Services\Dominion\GuardMembershipService;
use OpenDominion\Services\Dominion\ProtectionService;

class EspionageController extends AbstractDominionController
{
    public function getEspionage(Request $request)
    {
        $targetDominion = $request->input('dominion');

        return view('pages.dominion.espionage', [
            'espionageCalculator' => app(EspionageCalculator::class),
            'espionageHelper' => app(EspionageHelper::class),
            'governmentService' => app(GovernmentService::class),
            'guardMembershipService' => app(GuardMembershipService::class),
            'landCalculator' => app(LandCalculator::class),
            'militaryCalculator' => app(MilitaryCalculator::class),
            'protectionService' => app(ProtectionService::class),
            'rangeCalculator' => app(RangeCalculator::class),
            'targetDominion' => $targetDominion,
            'valuablesHelper' => app(ValuablesHelper::class),
        ]);
    }

    public function postEspionage(PerformEspionageRequest $request)
    {
        $dominion = $this->getSelectedDominion();
        $espionageActionService = app(EspionageActionService::class);

        try {
            $result = $espionageActionService->performOperation(
                $dominion,
                $request->get('operation'),
                Dominion::findOrFail($request->get('target_dominion'))
            );
        } catch (GameException $e) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors([$e->getMessage()]);
        }

        $request->session()->flash(('alert-' . ($result['alert-type'] ?? 'success')), $result['message']);

        $bountyRedirect = null;
        if (Str::contains($request->session()->previousUrl(), 'bounty-board')) {
            $bountyRedirect = route('dominion.bounty-board');
        }

        return redirect()
            ->to($bountyRedirect ?? $result['redirect'] ?? route('dominion.espionage'))
            ->with('target_dominion', $request->get('target_dominion'));
    }

    public function getInvestigate(Valuable $valuable)
    {
        $dominion = $this->getSelectedDominion();
        $militaryCalculator = app(MilitaryCalculator::class);
        $valuablesHelper = app(ValuablesHelper::class);

        // Make sure this valuable belongs to this dominion
        if ($valuable->source_dominion_id !== $dominion->id) {
            // TODO: Can use ->back() in place of redirect?
            return redirect()
                ->route('dominion.espionage')
                ->withErrors(['This valuable does not belong to you.']);
        }

        // Make sure this valuable is active and hasn't started investigation yet
        // TODO: Just make this change the display in the template (form disabled)
        if (!$valuable->isDiscovered() || $valuable->isAttempted() || $valuable->investigation_started_at !== null) {
            return redirect()
                ->route('dominion.espionage')
                ->withErrors(['This valuable cannot be investigated.']);
        }

        // Calculate available spies
        $totalSpies = (int) $militaryCalculator->getSpyCount($dominion);
        $spiesAssigned = $dominion->valuables()
            ->active()
            ->sum('spies_assigned');
        $availableSpies = max(0, $totalSpies - $spiesAssigned);

        return view('pages.dominion.valuables.investigate', [
            'valuable' => $valuable,
            'valuablesHelper' => $valuablesHelper,
            'availableSpies' => $availableSpies,
        ]);
    }

    public function postInvestigate(Request $request, Valuable $valuable)
    {
        $dominion = $this->getSelectedDominion();
        $espionageActionService = app(EspionageActionService::class);


        try {
            $result = $espionageActionService->startInvestigation(
                $dominion,
                $valuable->id,
                $request->input('spies_assigned')
            );
        } catch (GameException $e) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors([$e->getMessage()]);
        }

        $request->session()->flash(('alert-' . ($result['alert-type'] ?? 'success')), $result['message']);

        return redirect()->route('dominion.espionage');
    }
}
