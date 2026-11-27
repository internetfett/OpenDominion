<?php

namespace OpenDominion\Models;

use Database\Factories\RelicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An item a dominion found, bought or stole. It stays in the table after a market sale or a
 * salvage as history; sold_at marks it as gone.
 *
 * Several states are derived rather than stored: equipped (its loadout key is in the hero's
 * gear), salvaged (sold while bound) and stealable (held and unbound).
 *
 * @property int $id
 * @property int $round_id
 * @property int $dominion_id
 * @property int|null $previous_owner_dominion_id
 * @property string $category
 * @property string|null $item_key
 * @property string $name
 * @property string $rarity
 * @property string $source
 * @property \Illuminate\Support\Carbon $acquired_at
 * @property bool $is_bound
 * @property bool $is_listed
 * @property int|null $sold_price
 * @property \Illuminate\Support\Carbon|null $sold_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \OpenDominion\Models\Round $round
 * @property-read \OpenDominion\Models\Dominion $owner
 * @property-read \OpenDominion\Models\Dominion|null $previousOwner
 */
class Relic extends AbstractModel
{
    public const CATEGORY_EQUIPMENT = 'equipment';
    public const CATEGORY_ARTIFACT = 'artifact';
    public const CATEGORY_JEWELRY = 'jewelry';
    public const CATEGORY_ARTWORK = 'artwork';
    public const CATEGORY_TEXT = 'text';

    public const CATEGORIES = [
        self::CATEGORY_EQUIPMENT,
        self::CATEGORY_ARTIFACT,
        self::CATEGORY_JEWELRY,
        self::CATEGORY_ARTWORK,
        self::CATEGORY_TEXT,
    ];

    public const SOURCE_INFO_OP = 'info_op';
    public const SOURCE_SEARCH = 'search';
    public const SOURCE_PLUNDER = 'plunder';
    public const SOURCE_STOLEN = 'stolen';
    public const SOURCE_PURCHASED = 'purchased';
    public const SOURCE_LEGACY = 'legacy';

    /** Prefix of the hero loadout key that refers to an equipped relic, e.g. relic_12. */
    public const LOADOUT_KEY_PREFIX = 'relic_';

    use HasFactory;

    protected $table = 'relics';

    protected $casts = [
        'acquired_at' => 'datetime',
        'sold_at' => 'datetime',
        'is_bound' => 'boolean',
        'is_listed' => 'boolean',
        'sold_price' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Dominion::class, 'dominion_id');
    }

    public function previousOwner(): BelongsTo
    {
        return $this->belongsTo(Dominion::class, 'previous_owner_dominion_id');
    }

    // Eloquent Query Scopes

    /**
     * Relics still in someone's inventory.
     */
    public function scopeHeld(Builder $query): Builder
    {
        return $query->whereNull('sold_at');
    }

    /**
     * Relics a thief can take: held and never equipped.
     */
    public function scopeStealable(Builder $query): Builder
    {
        return $query->whereNull('sold_at')->where('is_bound', false);
    }

    public function scopeListed(Builder $query): Builder
    {
        return $query->whereNull('sold_at')->where('is_listed', true);
    }

    protected static function newFactory(): RelicFactory
    {
        return RelicFactory::new();
    }

    // Helpers

    public function isEquipment(): bool
    {
        return $this->category === self::CATEGORY_EQUIPMENT;
    }

    public function isHeld(): bool
    {
        return $this->sold_at === null;
    }

    public function wasSalvaged(): bool
    {
        return $this->sold_at !== null && $this->is_bound;
    }

    public function loadoutKey(): string
    {
        return self::LOADOUT_KEY_PREFIX . $this->id;
    }

    /**
     * Hours since the relic's appreciation clock started.
     */
    public function hoursHeld(): float
    {
        return $this->acquired_at->diffInSeconds(now()) / 3600;
    }
}
