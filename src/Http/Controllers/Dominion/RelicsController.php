<?php

namespace OpenDominion\Http\Controllers\Dominion;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenDominion\Calculators\Dominion\RelicCalculator;
use OpenDominion\Exceptions\GameException;
use OpenDominion\Helpers\RelicHelper;
use OpenDominion\Http\Requests\Dominion\Actions\RelicSearchActionRequest;
use OpenDominion\Models\Relic;
use OpenDominion\Services\Dominion\Actions\RelicActionService;

class RelicsController extends AbstractDominionController
{
    public function getIndex(): View
    {
        $dominion = $this->getSelectedDominion();

        $inventory = $dominion->relics()
            ->held()
            ->orderByDesc('acquired_at')
            ->get();

        $listings = Relic::query()
            ->listed()
            ->where('round_id', $dominion->round_id)
            ->where('dominion_id', '!=', $dominion->id)
            ->whereHas('owner', fn ($query) => $query->where('realm_id', $dominion->realm_id))
            ->with('owner')
            ->orderByDesc('acquired_at')
            ->get();

        $equippedKeys = (array) ($dominion->hero?->loadout['gear'] ?? []);

        return view('pages.dominion.relics', [
            'inventory' => $inventory,
            'listings' => $listings,
            'equippedKeys' => $equippedKeys,
            'relicCalculator' => app(RelicCalculator::class),
            'relicHelper' => app(RelicHelper::class),
        ]);
    }

    public function getHistory(): View
    {
        $dominion = $this->getSelectedDominion();

        $history = $dominion->relics()
            ->whereNotNull('sold_at')
            ->orderByDesc('sold_at')
            ->get();

        return view('pages.dominion.relics.history', [
            'history' => $history,
            'totalPlatinum' => (int) $history->sum('sold_price'),
            'relicHelper' => app(RelicHelper::class),
        ]);
    }

    public function postSearch(RelicSearchActionRequest $request): RedirectResponse
    {
        $dominion = $this->getSelectedDominion();

        return $this->perform($request, fn (RelicActionService $service) => $service->updateSearchSettings(
            $dominion,
            (int) $request->input('intensity'),
            $request->boolean('stop_on_find')
        ));
    }

    public function postSell(Request $request, Relic $relic): RedirectResponse
    {
        return $this->perform($request, fn (RelicActionService $service) => $service->sell($this->getSelectedDominion(), $relic));
    }

    public function postSalvage(Request $request, Relic $relic): RedirectResponse
    {
        return $this->perform($request, fn (RelicActionService $service) => $service->salvage($this->getSelectedDominion(), $relic));
    }

    public function postList(Request $request, Relic $relic): RedirectResponse
    {
        return $this->perform($request, fn (RelicActionService $service) => $service->list($this->getSelectedDominion(), $relic));
    }

    public function postUnlist(Request $request, Relic $relic): RedirectResponse
    {
        return $this->perform($request, fn (RelicActionService $service) => $service->unlist($this->getSelectedDominion(), $relic));
    }

    public function postPurchase(Request $request, Relic $relic): RedirectResponse
    {
        return $this->perform($request, fn (RelicActionService $service) => $service->purchase($this->getSelectedDominion(), $relic));
    }

    /**
     * Runs a relic action and returns to the relics page with its message or error.
     *
     * @param Closure(RelicActionService): array{message: string} $action
     */
    protected function perform(Request $request, Closure $action): RedirectResponse
    {
        try {
            $result = $action(app(RelicActionService::class));
        } catch (GameException $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
        }

        $request->session()->flash('alert-success', $result['message']);

        return redirect()->route('dominion.relics');
    }
}
