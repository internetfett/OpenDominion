<?php

namespace OpenDominion\Models;

/**
 * OpenDominion\Models\HeroBattleTurn
 *
 * @property int $id
 * @property int $hero_battle_id
 * @property int $turn
 * @property array $events structured BattleEvent payloads, in the order they happened
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \OpenDominion\Models\HeroBattle $battle
 * @method static \Illuminate\Database\Eloquent\Builder|\OpenDominion\Models\HeroBattleTurn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\OpenDominion\Models\HeroBattleTurn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\OpenDominion\Models\HeroBattleTurn query()
 * @mixin \Eloquent
 */
class HeroBattleTurn extends AbstractModel
{
    protected $casts = [
        'events' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function battle()
    {
        return $this->belongsTo(HeroBattle::class, 'hero_battle_id');
    }

    /**
     * Appends events to a turn's row, creating it on first use.
     *
     * @param array<int, array<string, mixed>> $events
     */
    public static function record(HeroBattle $heroBattle, int $turn, array $events): void
    {
        if ($events === []) {
            return;
        }

        $row = static::query()->firstOrNew(['hero_battle_id' => $heroBattle->id, 'turn' => $turn]);
        $row->events = array_merge($row->events ?? [], $events);
        $row->save();
    }
}
