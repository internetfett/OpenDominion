<?php

namespace OpenDominion\Services\Dominion;

use OpenDominion\Calculators\Dominion\RelicCalculator;
use OpenDominion\Helpers\RelicHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Relic;
use OpenDominion\Models\RelicTracking;

class RelicService
{
    public function __construct(
        protected RelicCalculator $relicCalculator,
        protected RelicHelper $relicHelper,
    ) {
    }

    /**
     * Whether a dominion can receive a relic right now: a human dominion past the round lock
     * with a free inventory slot.
     */
    public function canReceive(Dominion $dominion): bool
    {
        if ($dominion->user_id === null) {
            return false;
        }

        if ($this->relicCalculator->isRoundLocked($dominion->round)) {
            return false;
        }

        return !$this->relicCalculator->isInventoryFull($dominion);
    }

    /**
     * Creates a relic in the dominion's inventory. Every find goes through here. Returns null
     * when the dominion can't receive one.
     *
     * @param string|null $rarity the rarity to grant; rolled from the finder when null
     */
    public function grant(Dominion $dominion, string $source, ?string $rarity = null): ?Relic
    {
        if (!$this->canReceive($dominion)) {
            return null;
        }

        $rarity ??= $this->rollUpgrade($this->relicCalculator->getFinderRarity($dominion));
        $category = Relic::CATEGORIES[array_rand(Relic::CATEGORIES)];
        $equipment = $this->relicHelper->getEquipmentOfRarity($rarity);

        $relic = new Relic();
        $relic->round_id = $dominion->round_id;
        $relic->dominion_id = $dominion->id;
        $relic->rarity = $rarity;
        $relic->source = $source;
        $relic->acquired_at = now();
        $relic->is_bound = false;
        $relic->is_listed = false;

        if ($category === Relic::CATEGORY_EQUIPMENT && !empty($equipment)) {
            $item = $equipment[array_rand($equipment)];
            $relic->category = Relic::CATEGORY_EQUIPMENT;
            $relic->item_key = $item['key'];
            $relic->name = $item['name'];
        } else {
            $category = $category === Relic::CATEGORY_EQUIPMENT ? Relic::CATEGORY_ARTIFACT : $category;
            $relic->category = $category;
            $relic->name = $this->relicHelper->generateName($category, $rarity);
        }

        $relic->save();

        return $relic;
    }

    /**
     * Rolls the chance for a find to rise one rarity tier.
     */
    public function rollUpgrade(string $rarity): string
    {
        if (random_chance(config('relics.rarity_upgrade_chance'))) {
            return $this->relicHelper->getNextRarity($rarity);
        }

        return $rarity;
    }

    /**
     * Turns Search off once the dominion's inventory is full, so it stops draining strength.
     * Returns whether it did. The caller saves the dominion.
     */
    public function stopSearchIfFull(Dominion $dominion): bool
    {
        if ($this->relicCalculator->getSearchIntensity($dominion) === 0) {
            return false;
        }

        if (!$this->relicCalculator->isInventoryFull($dominion)) {
            return false;
        }

        $this->setSearchIntensity($dominion, 0);

        return true;
    }

    public function setSearchIntensity(Dominion $dominion, int $intensity): void
    {
        $settings = $dominion->settings ?? [];
        $settings['relics']['search_intensity'] = $intensity;
        $dominion->settings = $settings;
    }

    /**
     * Rolls an info-op or info-spell find. Returns a sentence to append to the op message, or
     * an empty string when nothing was found. The caller saves the attacker.
     */
    public function attemptInfoOpFind(Dominion $attacker, Dominion $target, string $agent = 'spies'): string
    {
        if (!$this->relicHelper->isSourceEnabled('info_ops') || $target->user_id === null || $attacker->id === $target->id) {
            return '';
        }

        if (!$this->canReceive($attacker)) {
            return '';
        }

        $tracking = RelicTracking::firstOrCreate(['dominion_id' => $attacker->id], ['progress' => 0]);
        $chance = config('relics.info_ops.base_chance') + $tracking->progress * config('relics.info_ops.chance_per_miss');

        if (!random_chance(min(1.0, $chance))) {
            $tracking->increment('progress');

            return '';
        }

        $relic = $this->grant($attacker, Relic::SOURCE_INFO_OP);
        $tracking->update(['progress' => 0]);

        $message = sprintf(
            ' Your %s have also found %s: %s!',
            $agent,
            $this->relicHelper->discoveryPhrase($relic->rarity, $relic->category),
            $relic->name
        );

        if ($this->stopSearchIfFull($attacker)) {
            $message .= ' Your inventory is now full, so Search has been turned off.';
        }

        return $message;
    }

    /**
     * Rolls plunder from a successful invasion, in rounds with attack finds turned on.
     * Rarity comes from the target's land.
     */
    public function attemptPlunder(Dominion $attacker, Dominion $target, float $range): ?Relic
    {
        if (!$this->relicHelper->isSourceEnabled('attacks') || $target->user_id === null) {
            return null;
        }

        if ($range < config('relics.plunder.min_range') || !$this->canReceive($attacker)) {
            return null;
        }

        if (!random_chance(config('relics.plunder.chance'))) {
            return null;
        }

        $relic = $this->grant($attacker, Relic::SOURCE_PLUNDER, $this->rollUpgrade($this->relicCalculator->getPlunderRarity($target)));
        $this->stopSearchIfFull($attacker);

        return $relic;
    }

    /**
     * Resolves a full Search meter at the tick: grants a relic, carries any overflow into the
     * next find, and turns Search off when set to stop after a find or when the inventory is
     * full. A meter that can't pay out stays full. Saves the dominion.
     *
     * @return array{relic: Relic|null, searchStopped: bool}
     */
    public function completeSearch(Dominion $dominion): array
    {
        $meterMax = config('relics.search.meter_max');

        if ($dominion->relic_search_meter < $meterMax) {
            return ['relic' => null, 'searchStopped' => false];
        }

        $relic = $this->grant($dominion, Relic::SOURCE_SEARCH);
        $searchStopped = false;

        if ($relic !== null) {
            $dominion->relic_search_meter -= $meterMax;

            if ($this->relicCalculator->isSearchStopOnFind($dominion)) {
                $this->setSearchIntensity($dominion, 0);
                $searchStopped = true;
            }
        } else {
            $dominion->relic_search_meter = $meterMax;
        }

        $searchStopped = $this->stopSearchIfFull($dominion) || $searchStopped;
        $dominion->save();

        return ['relic' => $relic, 'searchStopped' => $searchStopped];
    }

    /**
     * Moves a relic to a new owner, as if just found: unbound, unlisted, clock reset.
     */
    public function transfer(Relic $relic, Dominion $newOwner, string $source): void
    {
        $relic->previous_owner_dominion_id = $relic->dominion_id;
        $relic->dominion_id = $newOwner->id;
        $relic->source = $source;
        $relic->acquired_at = now();
        $relic->is_bound = false;
        $relic->is_listed = false;
        $relic->save();
    }

    /**
     * Picks one relic a thief can take from the target, uniformly at random, or null.
     */
    public function pickStealable(Dominion $target): ?Relic
    {
        return $target->relics()->stealable()->inRandomOrder()->first();
    }
}
