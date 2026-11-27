<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Relic;

/**
 * @extends Factory<Relic>
 */
class RelicFactory extends Factory
{
    protected $model = Relic::class;

    public function definition(): array
    {
        return [
            'category' => Relic::CATEGORY_ARTIFACT,
            'item_key' => null,
            'name' => 'Ancient ' . ucfirst($this->faker->word()),
            'rarity' => 'common',
            'source' => Relic::SOURCE_SEARCH,
            'acquired_at' => now(),
            'is_bound' => false,
            'is_listed' => false,
        ];
    }

    /**
     * Held by the given dominion.
     */
    public function ownedBy(Dominion $dominion): static
    {
        return $this->state(fn () => [
            'round_id' => $dominion->round_id,
            'dominion_id' => $dominion->id,
        ]);
    }

    /**
     * A piece of hand-written equipment from the data file.
     */
    public function equipment(string $itemKey, string $rarity): static
    {
        return $this->state(fn () => [
            'category' => Relic::CATEGORY_EQUIPMENT,
            'item_key' => $itemKey,
            'rarity' => $rarity,
        ]);
    }

    public function bound(): static
    {
        return $this->state(fn () => ['is_bound' => true]);
    }

    public function listed(): static
    {
        return $this->state(fn () => ['is_listed' => true]);
    }

    public function sold(int $price): static
    {
        return $this->state(fn () => ['sold_price' => $price, 'sold_at' => now()]);
    }
}
