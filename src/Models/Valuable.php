<?php

namespace OpenDominion\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * OpenDominion\Models\Valuable
 *
 * @property int $id
 * @property int $round_id
 * @property int $source_dominion_id
 * @property int $target_dominion_id
 * @property string $rarity
 * @property string $type
 * @property string $name
 * @property int $spies_assigned
 * @property \Illuminate\Support\Carbon|null $investigation_started_at
 * @property \Illuminate\Support\Carbon|null $attempted_at
 * @property bool $success
 * @property \Illuminate\Support\Carbon|null $sold_at
 * @property int|null $platinum_received
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \OpenDominion\Models\Round $round
 * @property-read \OpenDominion\Models\Dominion $sourceDominion
 * @property-read \OpenDominion\Models\Dominion $targetDominion
 * @method static \Illuminate\Database\Eloquent\Builder|\OpenDominion\Models\Valuable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\OpenDominion\Models\Valuable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\OpenDominion\Models\Valuable query()
 * @mixin \Eloquent
 */
class Valuable extends AbstractModel
{
    protected $table = 'valuables';

    protected $casts = [
        'success' => 'boolean',
        'spies_assigned' => 'integer',
        'platinum_received' => 'integer',
    ];

    protected $dates = [
        'investigation_started_at',
        'attempted_at',
        'sold_at',
        'created_at',
        'updated_at'
    ];

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    public function sourceDominion()
    {
        return $this->belongsTo(Dominion::class, 'source_dominion_id');
    }

    public function targetDominion()
    {
        return $this->belongsTo(Dominion::class, 'target_dominion_id');
    }

    /**
     * Check if the valuable has been discovered (created)
     */
    public function isDiscovered(): bool
    {
        return $this->created_at !== null;
    }

    /**
     * Check if investigation has started
     */
    public function isBeingInvestigated(): bool
    {
        return $this->investigation_started_at !== null && $this->attempted_at === null;
    }

    /**
     * Check if theft has been attempted
     */
    public function isAttempted(): bool
    {
        return $this->attempted_at !== null;
    }

    /**
     * Check if the theft was successful
     */
    public function isStolen(): bool
    {
        return $this->attempted_at !== null && $this->success === true;
    }

    /**
     * Check if the valuable has been sold
     */
    public function isSold(): bool
    {
        return $this->sold_at !== null;
    }

    // Eloquent Query Scopes

    /**
     * Scope to valuables discovered by a specific dominion
     */
    public function scopeDiscoveredBy(Builder $query, Dominion $dominion): Builder
    {
        return $query
            ->where('source_dominion_id', $dominion->id)
            ->where('round_id', $dominion->round_id);
    }

    /**
     * Scope to valuables that are being investigated (spies assigned, not yet attempted)
     */
    public function scopeBeingInvestigated(Builder $query): Builder
    {
        return $query
            ->whereNotNull('investigation_started_at')
            ->whereNull('attempted_at');
    }

    /**
     * Scope to valuables that are ready to steal (investigation complete, not yet attempted)
     */
    public function scopeStealable(Builder $query): Builder
    {
        return $query
            ->whereNotNull('investigation_started_at')
            ->whereNull('attempted_at');
    }

    /**
     * Scope to valuables that have been attempted
     */
    public function scopeAttempted(Builder $query): Builder
    {
        return $query->whereNotNull('attempted_at');
    }

    /**
     * Scope to successfully stolen valuables
     */
    public function scopeStolen(Builder $query): Builder
    {
        return $query
            ->whereNotNull('attempted_at')
            ->where('success', true);
    }

    /**
     * Scope to failed theft attempts
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query
            ->whereNotNull('attempted_at')
            ->where('success', false);
    }

    /**
     * Scope to valuables that can be sold (stolen and not yet sold)
     */
    public function scopeSellable(Builder $query): Builder
    {
        return $query
            ->whereNotNull('attempted_at')
            ->where('success', true)
            ->whereNull('sold_at');
    }

    /**
     * Scope to valuables that have been sold
     */
    public function scopeSold(Builder $query): Builder
    {
        return $query->whereNotNull('sold_at');
    }
}
