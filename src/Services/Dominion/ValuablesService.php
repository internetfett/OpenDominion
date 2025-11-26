<?php

namespace OpenDominion\Services\Dominion;

use Illuminate\Support\Facades\File;
use LogicException;
use OpenDominion\Helpers\ValuablesHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Valuable;

class ValuablesService
{
    /** @var ValuablesHelper */
    protected $valuablesHelper;

    public function __construct()
    {
        $this->valuablesHelper = app(ValuablesHelper::class);
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
        // Get all rarities and their discovery chances
        $rarities = $this->valuablesHelper->getValuableRarities();

        // Try to discover a valuable based on rarity chances
        foreach ($rarities as $rarity) {
            if (random_chance($rarity['discovery_chance'])) {
                return $this->createValuable($sourceDominion, $targetDominion, $rarity['key']);
            }
        }

        return null;
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

}
