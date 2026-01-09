<?php

namespace OpenDominion\Services\Dominion\Actions;

use DB;
use OpenDominion\Calculators\Dominion\EspionageCalculator;
use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Exceptions\GameException;
use OpenDominion\Helpers\ValuablesHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Valuable;
use OpenDominion\Services\Dominion\HistoryService;
use OpenDominion\Services\NotificationService;
use OpenDominion\Traits\DominionGuardsTrait;

class ValuableActionService
{
    use DominionGuardsTrait;

    /** @var ValuablesHelper */
    protected $valuablesHelper;

    /** @var MilitaryCalculator */
    protected $militaryCalculator;

    /** @var NotificationService */
    protected $notificationService;

    public function __construct()
    {
        $this->valuablesHelper = app(ValuablesHelper::class);
        $this->militaryCalculator = app(MilitaryCalculator::class);
        $this->notificationService = app(NotificationService::class);
    }

    /**
     * Start investigating a valuable
     */
    public function startInvestigation(Dominion $dominion, int $valuableId, int $spiesAssigned): array
    {
        $this->guardLockedDominion($dominion);
        $this->guardActionsDuringTick($dominion);

        // Find the valuable
        $valuable = $dominion->valuables()->find($valuableId);

        if (!$valuable) {
            throw new GameException('Valuable not found.');
        }

        // Make sure the valuable is discovered
        if (!$valuable->isDiscovered()) {
            throw new GameException('This valuable has not been discovered yet.');
        }

        // Make sure the valuable hasn't been completed yet
        if ($valuable->isCompleted()) {
            throw new GameException('This valuable has already been completed.');
        }

        // Make sure investigation hasn't already started
        if ($valuable->investigation_started_at !== null) {
            throw new GameException('Investigation has already started for this valuable.');
        }

        // NEW: Prevent investigation if listed for transfer
        if ($valuable->listed_for_transfer) {
            throw new GameException('This valuable is currently listed for transfer. Unlist it first before investigating.');
        }

        // Check staleness - accelerating curve from 0% to 100% over expiration window
        $hoursSinceDiscovery = now()->diffInHours($valuable->created_at);

        // Hard limit: guaranteed failure at expiration
        if ($hoursSinceDiscovery >= $this->valuablesHelper::EXPIRATION_HOURS) {
            DB::transaction(function () use ($valuable) {
                $valuable->completed_at = now();
                $valuable->success = false;
                $valuable->save();
            });

            return [
                'message' => 'Investigation failed - the information was too old and the valuable could not be located.',
                'alert-type' => 'danger',
            ];
        }

        // Accelerating staleness chance: (hours/expiration)^2
        $stalenessChance = pow($hoursSinceDiscovery / $this->valuablesHelper::EXPIRATION_HOURS, 2);

        if (random_chance($stalenessChance)) {
            DB::transaction(function () use ($valuable) {
                $valuable->completed_at = now();
                $valuable->success = false;
                $valuable->save();
            });

            return [
                'message' => 'Investigation failed - the information was too old and the valuable could not be located.',
                'alert-type' => 'danger',
            ];
        }

        // Calculate required spy-hours and min/max spies
        $requiredSpyHours = $this->valuablesHelper->getRequiredSpyHours($valuable);
        $minSpies = (int) ceil($requiredSpyHours / $this->valuablesHelper::MIN_INVESTIGATION_HOURS);
        $maxSpies = (int) ceil($requiredSpyHours / $this->valuablesHelper::MAX_INVESTIGATION_HOURS);

        // Validate spies assigned
        if ($spiesAssigned < $minSpies) {
            throw new GameException(sprintf(
                'You must assign at least %s %s for this valuable.',
                number_format($minSpies),
                str_plural('spy', $minSpies)
            ));
        }

        if ($spiesAssigned > $maxSpies) {
            throw new GameException(sprintf(
                'You cannot assign more than %s %s to this valuable.',
                number_format($maxSpies),
                str_plural('spy', $maxSpies)
            ));
        }

        // Validate that the resulting hours are a valid multiple of the step
        $hoursToComplete = (int) ceil($requiredSpyHours / $spiesAssigned);
        if ($hoursToComplete % $this->valuablesHelper::INVESTIGATION_HOUR_STEP !== 0) {
            throw new GameException('Invalid spy assignment - must result in investigation time that is a multiple of 6 hours.');
        }

        // Calculate available spies
        $totalSpies = (int) $this->militaryCalculator->getSpyCount($dominion);
        $currentSpiesAssigned = $dominion->valuables()
            ->active()
            ->where('id', '!=', $valuable->id)
            ->sum('spies_assigned');
        $availableSpies = max(0, $totalSpies - $currentSpiesAssigned);

        if ($spiesAssigned > $availableSpies) {
            throw new GameException(sprintf(
                'You only have %s %s available.',
                number_format($availableSpies),
                str_plural('spy', $availableSpies)
            ));
        }

        DB::transaction(function () use ($valuable, $spiesAssigned) {
            $valuable->spies_assigned = $spiesAssigned;
            $valuable->spy_hours = $this->valuablesHelper->calculateSpyHours($valuable);
            $valuable->investigation_started_at = now();

            // Calculate completion time aligned to hour boundaries (since tick processes hourly)
            $hoursToComplete = ceil($valuable->spy_hours / $spiesAssigned);
            $valuable->investigation_completes_at = now()->addHours($hoursToComplete)->startOfHour();

            $valuable->save();
        });

        return [
            'message' => sprintf(
                'You have assigned %s %s to investigate this valuable.',
                number_format($spiesAssigned),
                str_plural('spy', $spiesAssigned)
            ),
            'alert-type' => 'success',
        ];
    }

    /**
     * Cancel an ongoing investigation
     */
    public function cancelInvestigation(Dominion $dominion, Valuable $valuable): array
    {
        $this->guardLockedDominion($dominion);

        // Validate ownership
        if ($valuable->source_dominion_id !== $dominion->id) {
            throw new GameException('This valuable does not belong to you.');
        }

        // Validate investigation is in progress
        if (!$valuable->isBeingInvestigated()) {
            throw new GameException('This valuable is not being investigated.');
        }

        $spiesFreed = $valuable->spies_assigned;

        DB::transaction(function () use ($valuable) {
            // Reset investigation progress but keep valuable discovered
            $valuable->spies_assigned = 0;
            $valuable->spy_hours = null;
            $valuable->investigation_started_at = null;
            $valuable->investigation_completes_at = null;
            $valuable->save();
        });

        return [
            'message' => sprintf(
                'Investigation canceled. %s %s freed for reassignment.',
                number_format($spiesFreed),
                str_plural('spy', $spiesFreed)
            ),
            'alert-type' => 'success',
        ];
    }

    /**
     * Sell a stolen valuable
     */
    public function sellValuable(Dominion $dominion, Valuable $valuable): array
    {
        $this->guardLockedDominion($dominion);

        // Validate ownership
        if ($valuable->source_dominion_id !== $dominion->id) {
            throw new GameException('This valuable does not belong to you.');
        }

        // Validate stolen status
        if (!$valuable->isStolen()) {
            throw new GameException('This valuable has not been stolen yet.');
        }

        // Validate not already sold
        if ($valuable->isSold()) {
            throw new GameException('This valuable has already been sold.');
        }

        // Calculate current market price
        $espionageCalculator = app(EspionageCalculator::class);
        $salePrice = $espionageCalculator->getValuableSellPrice($valuable);

        DB::transaction(function () use ($dominion, $valuable, $salePrice) {
            // Update valuable
            $valuable->sold_at = now();
            $valuable->platinum_received = $salePrice;
            $valuable->save();

            // Give platinum to dominion
            $dominion->resource_platinum += $salePrice;
            $dominion->save(['event' => HistoryService::EVENT_ACTION_SELL_VALUABLE]);
        });

        return [
            'message' => sprintf(
                'You sold %s for %s platinum.',
                $valuable->name,
                number_format($salePrice)
            ),
            'alert-type' => 'success',
        ];
    }

    /**
     * List a valuable for transfer to realm mates
     */
    public function listForTransfer(Dominion $dominion, Valuable $valuable): array
    {
        $this->guardLockedDominion($dominion);

        if ($valuable->source_dominion_id !== $dominion->id) {
            throw new GameException('This valuable does not belong to you.');
        }

        if (!$valuable->isEligibleForTransfer()) {
            throw new GameException('This valuable cannot be listed for transfer.');
        }

        $price = $this->valuablesHelper->getTransferPrice($valuable);

        DB::transaction(function () use ($valuable) {
            $valuable->listed_for_transfer = true;
            $valuable->save();
        });

        return [
            'message' => sprintf(
                'You have listed %s for transfer to your realm for %s platinum.',
                $valuable->name,
                number_format($price)
            ),
            'alert-type' => 'success',
        ];
    }

    /**
     * Remove a valuable from transfer listing
     */
    public function unlistFromTransfer(Dominion $dominion, Valuable $valuable): array
    {
        $this->guardLockedDominion($dominion);

        if ($valuable->source_dominion_id !== $dominion->id) {
            throw new GameException('This valuable does not belong to you.');
        }

        if (!$valuable->isListedForTransfer()) {
            throw new GameException('This valuable is not listed for transfer.');
        }

        DB::transaction(function () use ($valuable) {
            $valuable->listed_for_transfer = false;
            $valuable->save();
        });

        return [
            'message' => sprintf('You have removed %s from the realm marketplace.', $valuable->name),
            'alert-type' => 'success',
        ];
    }

    /**
     * Transfer a valuable to a realm mate
     */
    public function transferToRealmMate(Dominion $buyer, Valuable $valuable): array
    {
        $this->guardLockedDominion($buyer);

        $seller = $valuable->sourceDominion;

        if (!$valuable->isListedForTransfer()) {
            throw new GameException('This valuable is not available for transfer.');
        }

        if ($buyer->realm_id !== $seller->realm_id) {
            throw new GameException('You can only receive valuables from your realm mates.');
        }

        if ($buyer->id === $seller->id) {
            throw new GameException('You cannot transfer valuables to yourself.');
        }

        $price = $this->valuablesHelper->getTransferPrice($valuable);

        if ($buyer->resource_platinum < $price) {
            throw new GameException(sprintf(
                'You need %s platinum to receive this valuable.',
                number_format($price)
            ));
        }

        DB::transaction(function () use ($buyer, $seller, $valuable, $price) {
            // Transfer platinum from buyer to seller
            $buyer->resource_platinum -= $price;
            $buyer->save(['event' => HistoryService::EVENT_ACTION_PURCHASE_VALUABLE]);

            $seller->resource_platinum += $price;
            $seller->save(['event' => HistoryService::EVENT_ACTION_TRANSFER_VALUABLE]);

            // Transfer valuable to buyer (reset to discovered state)
            $valuable->source_dominion_id = $buyer->id;
            $valuable->spies_assigned = 0;
            $valuable->investigation_started_at = null;
            $valuable->investigation_completes_at = null;
            $valuable->listed_for_transfer = false;
            $valuable->transferred = true;
            $valuable->save();

            // Notify seller
            $this->notificationService->notify(
                $seller,
                sprintf(
                    '%s received your %s for %s platinum.',
                    $buyer->name,
                    $valuable->name,
                    number_format($price)
                )
            );
        });

        return [
            'message' => sprintf(
                'You have received %s from %s for %s platinum.',
                $valuable->name,
                $seller->name,
                number_format($price)
            ),
            'alert-type' => 'success',
        ];
    }
}
