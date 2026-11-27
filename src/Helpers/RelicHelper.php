<?php

namespace OpenDominion\Helpers;

use OpenDominion\Models\Relic;
use RuntimeException;

class RelicHelper
{
    public const RARITY_COMMON = 'common';
    public const RARITY_UNCOMMON = 'uncommon';
    public const RARITY_RARE = 'rare';
    public const RARITY_EPIC = 'epic';
    public const RARITY_LEGENDARY = 'legendary';

    /** @var array|null parsed data file */
    protected ?array $dataCache = null;

    /**
     * The rarity table, lowest first, keyed by rarity.
     *
     * @return array<string, array{label: string, class: string, min: int, max: int}>
     */
    public function getRarities(): array
    {
        return config('relics.rarities');
    }

    /**
     * @return array{label: string, class: string, min: int, max: int}
     */
    public function getRarity(string $rarity): array
    {
        $rarities = $this->getRarities();

        if (!isset($rarities[$rarity])) {
            throw new RuntimeException("Unknown relic rarity: {$rarity}");
        }

        return $rarities[$rarity];
    }

    /**
     * The rarity one tier above, or the same rarity at the top tier.
     */
    public function getNextRarity(string $rarity): string
    {
        $keys = array_keys($this->getRarities());
        $index = array_search($rarity, $keys, true);

        return $keys[min(count($keys) - 1, $index + 1)];
    }

    public function getRarityLabel(string $rarity): string
    {
        return $this->getRarity($rarity)['label'];
    }

    public function getRarityClass(string $rarity): string
    {
        return $this->getRarity($rarity)['class'];
    }

    /**
     * The themed display name, e.g. "Relic" or "Plunder".
     */
    public function getName(bool $plural = false): string
    {
        return config($plural ? 'relics.name_plural' : 'relics.name_singular');
    }

    public function isSourceEnabled(string $source): bool
    {
        return (bool) config("relics.sources.{$source}");
    }

    public function getCategoryLabel(string $category): string
    {
        return ucfirst($category);
    }

    /**
     * Discovery phrase for messages, e.g. "a Rare piece of equipment" or "an Epic artifact".
     */
    public function discoveryPhrase(string $rarity, string $category): string
    {
        $noun = match ($category) {
            Relic::CATEGORY_EQUIPMENT => 'piece of equipment',
            Relic::CATEGORY_JEWELRY => 'item of jewelry',
            Relic::CATEGORY_ARTWORK => 'work of art',
            default => $category,
        };

        $rarityLabel = $this->getRarityLabel($rarity);
        $article = preg_match('/^[aeiou]/i', $rarityLabel) ? 'an' : 'a';

        return "{$article} {$rarityLabel} {$noun}";
    }

    /**
     * Hand-written equipment, keyed by item key.
     *
     * @return array<string, array{key: string, name: string, rarity: string, icon: string, stats: array<string, int>}>
     */
    public function getEquipment(): array
    {
        return collect($this->loadData()['equipment'])->keyBy('key')->all();
    }

    /**
     * @return array{key: string, name: string, rarity: string, icon: string, stats: array<string, int>}|null
     */
    public function getEquipmentItem(?string $itemKey): ?array
    {
        if ($itemKey === null) {
            return null;
        }

        return $this->getEquipment()[$itemKey] ?? null;
    }

    /**
     * Equipment items of one rarity.
     *
     * @return array<int, array{key: string, name: string, rarity: string, icon: string, stats: array<string, int>}>
     */
    public function getEquipmentOfRarity(string $rarity): array
    {
        return array_values(array_filter($this->getEquipment(), fn (array $item) => $item['rarity'] === $rarity));
    }

    /**
     * An equipment relic's stats for display, e.g. "+2 Attack, -10 Evasion", or an empty
     * string for anything that isn't equipment.
     */
    public function describeStats(Relic $relic, string $separator = ', '): string
    {
        $item = $relic->isEquipment() ? $this->getEquipmentItem($relic->item_key) : null;

        if ($item === null) {
            return '';
        }

        return collect($item['stats'])
            ->map(fn (int $value, string $stat) => sprintf('%+d %s', $value, ucfirst($stat)))
            ->implode($separator);
    }

    /**
     * Categories whose names are generated from word pools.
     *
     * @return string[]
     */
    public function getGeneratedCategories(): array
    {
        return array_keys($this->loadData()['generated']);
    }

    /**
     * Generates a name for a sale-only relic of the given category and rarity.
     */
    public function generateName(string $category, string $rarity): string
    {
        $pools = $this->loadData()['generated'][$category] ?? null;

        if ($pools === null) {
            throw new RuntimeException("Unknown generated relic category: {$category}");
        }

        if ($rarity === self::RARITY_EPIC || $rarity === self::RARITY_LEGENDARY) {
            $list = $pools[$rarity] ?? [];
            if (empty($list)) {
                throw new RuntimeException("No {$rarity} names for category {$category}");
            }

            return $list[array_rand($list)];
        }

        $patterns = $this->patternsForRarity($rarity);

        return $this->fillPattern($patterns[array_rand($patterns)], $pools);
    }

    /**
     * Loads (and caches) the configured data file.
     */
    protected function loadData(): array
    {
        if ($this->dataCache !== null) {
            return $this->dataCache;
        }

        $path = base_path('app/data/' . config('relics.data_file'));
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("Unable to read relic data at {$path}");
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['generated'], $decoded['equipment'])) {
            throw new RuntimeException("Relic data at {$path} could not be parsed.");
        }

        return $this->dataCache = $decoded;
    }

    /**
     * Naming patterns for a rarity. Each pattern is a list of segments: literals, or '@key'
     * for a random pick from that word pool.
     *
     * @return array<int, string[]>
     */
    protected function patternsForRarity(string $rarity): array
    {
        $adjBase = ['@adjectives', ' ', '@bases'];
        $matBase = ['@materials', ' ', '@bases'];
        $theAdjBase = ['The ', '@adjectives', ' ', '@bases'];
        $baseOfPlace = ['@bases', ' of ', '@places'];
        $possBase = ['@possessives', ' ', '@bases'];
        $baseOfEvent = ['@bases', ' of the ', '@events'];

        return match ($rarity) {
            self::RARITY_COMMON => [$adjBase, $matBase],
            self::RARITY_UNCOMMON => [$adjBase, $matBase, $theAdjBase],
            default => [$adjBase, $matBase, $theAdjBase, $baseOfPlace, $possBase, $baseOfEvent],
        };
    }

    /**
     * @param string[] $pattern
     */
    protected function fillPattern(array $pattern, array $pools): string
    {
        $out = '';
        foreach ($pattern as $segment) {
            if ($segment !== '' && $segment[0] === '@') {
                $pool = $pools[substr($segment, 1)] ?? [];
                if (empty($pool)) {
                    throw new RuntimeException("Empty word pool for key {$segment}");
                }
                $out .= $pool[array_rand($pool)];
            } else {
                $out .= $segment;
            }
        }

        return $out;
    }
}
