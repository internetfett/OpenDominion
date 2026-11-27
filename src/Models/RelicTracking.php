<?php

namespace OpenDominion\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A dominion's progress toward its next info-op relic find. Each eligible info op that finds
 * nothing adds a point, raising the next roll's chance; a find resets it.
 *
 * @property int $id
 * @property int $dominion_id
 * @property int $progress
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \OpenDominion\Models\Dominion $dominion
 */
class RelicTracking extends AbstractModel
{
    protected $table = 'relic_tracking';

    protected $casts = [
        'progress' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function dominion(): BelongsTo
    {
        return $this->belongsTo(Dominion::class);
    }
}
