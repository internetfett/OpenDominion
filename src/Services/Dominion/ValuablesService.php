<?php

namespace OpenDominion\Services\Dominion;

use DB;
use Illuminate\Support\Facades\File;
use LogicException;
use OpenDominion\Calculators\Dominion\LandCalculator;
use OpenDominion\Calculators\Dominion\MilitaryCalculator;
use OpenDominion\Helpers\ValuablesHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Round;
use OpenDominion\Models\Valuable;

class ValuablesService
{
    /** @var ValuablesHelper */
    protected $valuablesHelper;

    /** @var LandCalculator */
    protected $landCalculator;

    /** @var MilitaryCalculator */
    protected $militaryCalculator;

    public function __construct()
    {
        $this->valuablesHelper = app(ValuablesHelper::class);
        $this->landCalculator = app(LandCalculator::class);
        $this->militaryCalculator = app(MilitaryCalculator::class);
    }

    /**
     * Attempt to discover a valuable during an info gathering operation
     *
     * @param Dominion $sourceDominion
     * @param Dominion $targetDominion
     * @return Valuable|null
     */
    public function attemptDiscovery(Dominion $sourceDominion, Dominion $targetDominion): ?Valuable
    {
        // 1% chance to discover any valuable
        if (!random_chance($this->valuablesHelper::DISCOVERY_CHANCE)) {
            return null;
        }

        // Calculate combined metric to determine rarity
        // Land score: normalized between 500 (0.0) and 8000 (1.0)
        $targetLand = $this->landCalculator->getTotalLand($targetDominion);
        $landScore = min(1.0, max(0, ($targetLand - 500) / 7500));

        // Spy score: raw spy ratio (already 0-1, typically around 0.2)
        $attackerSpyRatio = $this->militaryCalculator->getSpyRatio($sourceDominion);
        $spyScore = min(1.0, $attackerSpyRatio);

        // Combined metric: average of both scores
        $rarityScore = ($landScore + $spyScore) / 2;

        // Select rarity based on score
        $rarity = $this->selectRarityByMetric($rarityScore);

        return $this->createValuable($sourceDominion, $targetDominion, $rarity);
    }

    /**
     * Select rarity tier based on combined metric score
     *
     * @param float $score Combined metric score (0.0 to 1.0)
     * @return string Rarity key
     */
    protected function selectRarityByMetric(float $score): string
    {
        $rarities = $this->valuablesHelper->getValuableRarities();

        // Map score to rarity index using linear distribution
        // Lower scores → common rarities, higher scores → rare rarities
        $maxIndex = count($rarities) - 1;
        $index = (int) round($score * $maxIndex);

        return $rarities[$index]['key'];
    }

    /**
     * Create a new valuable
     *
     * @param Dominion $sourceDominion
     * @param Dominion $targetDominion
     * @param string $rarity
     * @return Valuable
     * @throws LogicException
     */
    public function createValuable(Dominion $sourceDominion, Dominion $targetDominion, string $rarity): Valuable
    {
        $rarityInfo = $this->valuablesHelper->getValuableRarityInfo($rarity);
        if (!$rarityInfo) {
            throw new LogicException("Invalid rarity: {$rarity}");
        }

        // Get random type
        $types = $this->valuablesHelper->getValuableTypes();
        $type = $types->random();

        // Generate name using prefix + base + suffix
        $name = $this->generateValuableName($type);

        return Valuable::create([
            'round_id' => $sourceDominion->round_id,
            'source_dominion_id' => $sourceDominion->id,
            'target_dominion_id' => $targetDominion->id,
            'rarity' => $rarity,
            'type' => $type,
            'name' => $name,
            'spies_assigned' => 0,
            'success' => false,
        ]);
    }

    /**
     * Generate a valuable name using prefix + base + suffix from JSON data
     *
     * @param string $type
     * @return string
     */
    protected function generateValuableName(string $type): string
    {
        $path = base_path('app/data/valuables.json');
        $valuablesData = [];

        if (File::exists($path)) {
            $valuablesData = json_decode(File::get($path), true);
        }

        if (!isset($valuablesData[$type])) {
            return "Unknown {$type}";
        }

        $data = $valuablesData[$type];

        // Randomly select prefix, base, and suffix
        $prefix = $data['prefixes'][array_rand($data['prefixes'])];
        $base = $data['bases'][array_rand($data['bases'])];
        $suffix = $data['suffixes'][array_rand($data['suffixes'])];

        return "{$prefix} {$base} {$suffix}";
    }

    /**
     * Process completed valuable investigations (called by hourly tick)
     *
     * @param Round $round
     * @return void
     */
    public function processCompletedInvestigations(Round $round): void
    {
        // Find investigations ready for automatic theft
        $readyThefts = Valuable::where('round_id', $round->id)
            ->whereNotNull('investigation_completes_at')
            ->where('investigation_completes_at', '<=', now())
            ->whereNull('completed_at')
            ->get();

        foreach ($readyThefts as $valuable) {
            DB::transaction(function () use ($valuable) {
                $valuable->completed_at = now();
                $valuable->success = true;
                $valuable->save();
            }, 5);
        }

        // Find expired valuables (48 hours after discovery, not yet completed)
        $expiredValuables = Valuable::where('round_id', $round->id)
            ->where('created_at', '<=', now()->subHours($this->valuablesHelper::EXPIRATION_HOURS))
            ->whereNull('completed_at')
            ->get();

        foreach ($expiredValuables as $valuable) {
            DB::transaction(function () use ($valuable) {
                $valuable->completed_at = now();
                $valuable->success = false;
                $valuable->save();
            }, 5);
        }
    }

}
