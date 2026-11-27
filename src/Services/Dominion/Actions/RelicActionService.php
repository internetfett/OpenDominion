<?php

namespace OpenDominion\Services\Dominion\Actions;

use DB;
use OpenDominion\Calculators\Dominion\HeroCalculator;
use OpenDominion\Calculators\Dominion\RelicCalculator;
use OpenDominion\Exceptions\GameException;
use OpenDominion\Helpers\RelicHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Relic;
use OpenDominion\Services\Dominion\HistoryService;
use OpenDominion\Services\Dominion\RelicService;
use OpenDominion\Services\NotificationService;
use OpenDominion\Traits\DominionGuardsTrait;

class RelicActionService
{
    use DominionGuardsTrait;

    public function __construct(
        protected HeroCalculator $heroCalculator,
        protected NotificationService $notificationService,
        protected RelicCalculator $relicCalculator,
        protected RelicHelper $relicHelper,
        protected RelicService $relicService,
    ) {
    }

    /**
     * Sells an unbound relic on the black market for its current value. The relic leaves the
     * game but its row stays as history.
     */
    public function sell(Dominion $dominion, Relic $relic): array
    {
        $this->guardLockedDominion($dominion);
        $this->guardActionsDuringTick($dominion);
        $this->guardHeldBy($dominion, $relic);

        if ($relic->is_bound) {
            throw new GameException("{$relic->name} has been bound to your hero and can only be salvaged.");
        }

        $price = $this->relicCalculator->getMarketPrice($relic);

        DB::transaction(function () use ($dominion, $relic, $price) {
            $dominion->resource_platinum += $price;
            $relic->is_listed = false;
            $relic->sold_price = $price;
            $relic->sold_at = now();
            $relic->save();
            $dominion->save(['event' => HistoryService::EVENT_ACTION_SELL_RELIC]);
        });

        return [
            'message' => sprintf('You sell %s for %s platinum.', $relic->name, number_format($price)),
        ];
    }

    /**
     * Salvages a bound relic for a fraction of its rarity's minimum, freeing its slot.
     */
    public function salvage(Dominion $dominion, Relic $relic): array
    {
        $this->guardLockedDominion($dominion);
        $this->guardActionsDuringTick($dominion);
        $this->guardHeldBy($dominion, $relic);

        if (!$relic->is_bound) {
            throw new GameException("Only bound {$this->pluralName()} can be salvaged. Sell {$relic->name} instead.");
        }

        if ($this->isEquipped($dominion, $relic)) {
            throw new GameException("Unequip {$relic->name} before salvaging it.");
        }

        $value = $this->relicCalculator->getSalvageValue($relic->rarity);

        DB::transaction(function () use ($dominion, $relic, $value) {
            $dominion->resource_platinum += $value;
            $relic->sold_price = $value;
            $relic->sold_at = now();
            $relic->save();
            $dominion->save(['event' => HistoryService::EVENT_ACTION_SALVAGE_RELIC]);
        });

        return [
            'message' => sprintf('You salvage %s for %s platinum.', $relic->name, number_format($value)),
        ];
    }

    /**
     * Lists an unbound relic for realmmates to buy at its realm price. It stays stealable.
     */
    public function list(Dominion $dominion, Relic $relic): array
    {
        $this->guardLockedDominion($dominion);
        $this->guardHeldBy($dominion, $relic);

        if ($relic->is_bound) {
            throw new GameException("{$relic->name} has been bound to your hero and can't be sold.");
        }

        if ($relic->is_listed) {
            throw new GameException("{$relic->name} is already listed.");
        }

        $relic->is_listed = true;
        $relic->save();

        return [
            'message' => sprintf('%s is now listed for %s platinum.', $relic->name, number_format($this->relicCalculator->getRealmPrice($relic->rarity))),
        ];
    }

    public function unlist(Dominion $dominion, Relic $relic): array
    {
        $this->guardLockedDominion($dominion);
        $this->guardHeldBy($dominion, $relic);

        if (!$relic->is_listed) {
            throw new GameException("{$relic->name} is not listed.");
        }

        $relic->is_listed = false;
        $relic->save();

        return [
            'message' => sprintf('%s has been taken off the market.', $relic->name),
        ];
    }

    /**
     * Buys a realmmate's listing at its realm price. The relic arrives unbound with its clock
     * reset, as if just found.
     */
    public function purchase(Dominion $buyer, Relic $relic): array
    {
        $this->guardLockedDominion($buyer);
        $this->guardActionsDuringTick($buyer);

        if (!$relic->isHeld() || !$relic->is_listed) {
            throw new GameException('That listing is no longer available.');
        }

        $seller = $relic->owner;

        if ($seller->id === $buyer->id) {
            throw new GameException('You cannot purchase your own listing.');
        }

        if ($seller->realm_id !== $buyer->realm_id) {
            throw new GameException('You can only purchase listings from your own realmmates.');
        }

        if ($this->relicCalculator->isRoundLocked($buyer->round)) {
            throw new GameException("You cannot buy {$this->pluralName()} for the first three days of the round.");
        }

        if ($this->relicCalculator->isInventoryFull($buyer)) {
            throw new GameException("Your inventory is full. Sell or salvage some of your {$this->pluralName()} first.");
        }

        $price = $this->relicCalculator->getRealmPrice($relic->rarity);

        if ($buyer->resource_platinum < $price) {
            throw new GameException(sprintf('You need %s platinum to purchase this listing.', number_format($price)));
        }

        $searchStopped = false;

        DB::transaction(function () use ($buyer, $seller, $relic, $price, &$searchStopped) {
            $buyer->resource_platinum -= $price;
            $seller->resource_platinum += $price;

            $this->relicService->transfer($relic, $buyer, Relic::SOURCE_PURCHASED);
            $searchStopped = $this->relicService->stopSearchIfFull($buyer);

            $buyer->save(['event' => HistoryService::EVENT_ACTION_PURCHASE_RELIC]);
            $seller->save(['event' => HistoryService::EVENT_ACTION_TRANSFER_RELIC]);
        });

        $this->notificationService
            ->queueNotification('relic_purchased', [
                'buyerDominionId' => $buyer->id,
                'relicName' => $relic->name,
                'price' => $price,
            ])
            ->sendNotifications($seller, 'irregular_dominion');

        $message = sprintf('You purchased %s from %s for %s platinum.', $relic->name, $seller->name, number_format($price));
        if ($searchStopped) {
            $message .= ' Your inventory is now full, so Search has been turned off.';
        }

        return ['message' => $message];
    }

    /**
     * Sets the Search intensity (0 turns it off) and whether it stops after a find.
     */
    public function updateSearchSettings(Dominion $dominion, int $intensity, bool $stopOnFind): array
    {
        $this->guardLockedDominion($dominion);
        $this->guardActionsDuringTick($dominion);

        if ($intensity !== 0 && !in_array($intensity, config('relics.search.intensities'), true)) {
            throw new GameException('Invalid Search intensity.');
        }

        if ($intensity !== 0 && !$this->relicHelper->isSourceEnabled('search')) {
            throw new GameException('Search is not available this round.');
        }

        if ($intensity !== 0 && $this->relicCalculator->isInventoryFull($dominion)) {
            throw new GameException("Your inventory is full. Sell or salvage some of your {$this->pluralName()} before searching.");
        }

        $this->relicService->setSearchIntensity($dominion, $intensity);
        $settings = $dominion->settings;
        $settings['relics']['search_stop_on_find'] = $stopOnFind;
        $dominion->settings = $settings;
        $dominion->save();

        return [
            'message' => $intensity === 0
                ? 'Search has been turned off.'
                : sprintf('Your spies now spend %d%% spy strength each hour searching.', $intensity),
        ];
    }

    /**
     * Whether the relic is in the dominion's hero loadout.
     */
    public function isEquipped(Dominion $dominion, Relic $relic): bool
    {
        if ($dominion->hero === null) {
            return false;
        }

        return in_array($relic->loadoutKey(), (array) ($dominion->hero->loadout['gear'] ?? []), true);
    }

    protected function guardHeldBy(Dominion $dominion, Relic $relic): void
    {
        if ($relic->dominion_id !== $dominion->id || !$relic->isHeld()) {
            throw new GameException("That {$this->singularName()} does not belong to you.");
        }
    }

    protected function singularName(): string
    {
        return strtolower($this->relicHelper->getName());
    }

    protected function pluralName(): string
    {
        return strtolower($this->relicHelper->getName(true));
    }
}
