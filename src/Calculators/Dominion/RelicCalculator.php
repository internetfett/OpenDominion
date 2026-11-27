<?php

namespace OpenDominion\Calculators\Dominion;

use OpenDominion\Helpers\RelicHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Relic;
use OpenDominion\Models\Round;
use OpenDominion\Services\Dominion\ProtectionService;

class RelicCalculator
{
    public function __construct(
        protected LandCalculator $landCalculator,
        protected MilitaryCalculator $militaryCalculator,
        protected ProtectionService $protectionService,
        protected RelicHelper $relicHelper,
    ) {
    }

    /**
     * Current market value: climbs in a straight line from the rarity's min to its max over
     * the appreciation window, then holds at the max.
     */
    public function getMarketPrice(Relic $relic): int
    {
        $rarity = $this->relicHelper->getRarity($relic->rarity);
        $progress = min(1.0, $relic->hoursHeld() / config('relics.appreciation_hours'));

        return (int) round($rarity['min'] + ($rarity['max'] - $rarity['min']) * $progress);
    }

    /**
     * Whole hours until a relic reaches its maximum market value, 0 once it has.
     */
    public function getHoursUntilMaxValue(Relic $relic): int
    {
        return (int) max(0, ceil(config('relics.appreciation_hours') - $relic->hoursHeld()));
    }

    /**
     * Flat price for a realm sale: the rarity's minimum, so buying and selling straight away
     * only breaks even.
     */
    public function getRealmPrice(string $rarity): int
    {
        return $this->relicHelper->getRarity($rarity)['min'];
    }

    public function getSalvageValue(string $rarity): int
    {
        return (int) round($this->relicHelper->getRarity($rarity)['min'] * config('relics.salvage_ratio'));
    }

    /**
     * Whether the round is still too young for any relic to be found, bought or stolen.
     */
    public function isRoundLocked(Round $round): bool
    {
        return $round->start_date->diffInHours(now()) < config('relics.round_lock_hours');
    }

    public function getHeldCount(Dominion $dominion): int
    {
        return $dominion->relics()->held()->count();
    }

    public function isInventoryFull(Dominion $dominion): bool
    {
        return $this->getHeldCount($dominion) >= config('relics.inventory_cap');
    }

    /**
     * Whether a dominion is on high alert after a recent theft: a relic stolen from it within
     * the window means Steal Relic can't target it.
     */
    public function isOnHighAlert(Dominion $dominion): bool
    {
        return Relic::query()
            ->where('previous_owner_dominion_id', $dominion->id)
            ->where('source', Relic::SOURCE_STOLEN)
            ->where('acquired_at', '>', now()->subHours(config('relics.steal.high_alert_hours')))
            ->exists();
    }

    /**
     * The Search intensity the dominion has chosen: spy strength drained per hour, 0 when off.
     */
    public function getSearchIntensity(Dominion $dominion): int
    {
        $intensity = (int) $dominion->getSetting('relics.search_intensity');

        return in_array($intensity, config('relics.search.intensities'), true) ? $intensity : 0;
    }

    public function isSearchStopOnFind(Dominion $dominion): bool
    {
        return (bool) ($dominion->getSetting('relics.search_stop_on_find') ?? true);
    }

    /**
     * Spy strength Search takes this hour. Zero when Search is off or can't run: before the
     * round lock ends, under protection, or with less strength than the drain.
     */
    public function getSearchDrain(Dominion $dominion): float
    {
        $intensity = $this->getSearchIntensity($dominion);

        if ($intensity === 0 || !$this->relicHelper->isSourceEnabled('search')) {
            return 0;
        }

        if ($this->isRoundLocked($dominion->round) || $this->protectionService->isUnderProtection($dominion)) {
            return 0;
        }

        if ($dominion->spy_strength < $intensity) {
            return 0;
        }

        return $intensity;
    }

    /**
     * Meter progress Search makes at a given intensity, which scales steeply with spy ratio
     * above the threshold.
     */
    public function getSearchProgressAt(Dominion $dominion, int $intensity): float
    {
        $spyRatio = $this->militaryCalculator->getSpyRatio($dominion, 'offense');

        return $intensity * config('relics.search.rate') * max(0, $spyRatio - config('relics.search.threshold'));
    }

    /**
     * Meter progress Search makes this hour: none unless it drains strength.
     */
    public function getSearchProgress(Dominion $dominion): float
    {
        $drain = (int) $this->getSearchDrain($dominion);

        return $drain > 0 ? $this->getSearchProgressAt($dominion, $drain) : 0;
    }

    /**
     * Hours until the meter fills at the chosen intensity, or null when Search is off or the
     * spy ratio is too low for it to make progress.
     */
    public function getHoursUntilFind(Dominion $dominion): ?int
    {
        $intensity = $this->getSearchIntensity($dominion);

        if ($intensity === 0) {
            return null;
        }

        $progress = $this->getSearchProgressAt($dominion, $intensity);

        if ($progress <= 0) {
            return null;
        }

        $remaining = max(0, config('relics.search.meter_max') - $dominion->relic_search_meter);

        return (int) ceil($remaining / $progress);
    }

    /**
     * A 0–1 score from land and spy ratio, averaged and each capped to 0–1.
     */
    public function getRarityScore(int $land, float $spyRatio): float
    {
        $landScore = max(0.0, min(1.0, ($land - 350) / 5650));
        $spyScore = max(0.0, min(1.0, $spyRatio));

        return ($landScore + $spyScore) / 2;
    }

    /**
     * The rarity for a 0–1 score: five equal bands, lowest first.
     */
    public function getRarityForScore(float $score): string
    {
        $rarities = array_keys($this->relicHelper->getRarities());
        $index = (int) min(count($rarities) - 1, floor($score * count($rarities)));

        return $rarities[max(0, $index)];
    }

    /**
     * The rarity a dominion's own land and offensive spy ratio earn, before any upgrade roll.
     */
    public function getFinderRarity(Dominion $dominion): string
    {
        return $this->getRarityForScore($this->getRarityScore(
            $this->landCalculator->getTotalLand($dominion),
            $this->militaryCalculator->getSpyRatio($dominion, 'offense')
        ));
    }

    /**
     * The rarity plunder from a target earns: the target's land alone.
     */
    public function getPlunderRarity(Dominion $target): string
    {
        $land = $this->landCalculator->getTotalLand($target);

        return $this->getRarityForScore(max(0.0, min(1.0, ($land - 350) / 5650)));
    }
}
