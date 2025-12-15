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
 * @property int|null $spy_hours
 * @property \Illuminate\Support\Carbon|null $investigation_started_at
 * @property \Illuminate\Support\Carbon|null $investigation_completes_at
 * @property \Illuminate\Support\Carbon|null $completed_at
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
        'investigation_completes_at',
        'completed_at',
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
        return $this->investigation_started_at !== null && $this->completed_at === null;
    }

    /**
     * Check if theft has been completed
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Check if the theft was successful
     */
    public function isStolen(): bool
    {
        return $this->completed_at !== null && $this->success === true;
    }

    /**
     * Check if investigation is ready for automatic theft
     */
    public function isReadyForTheft(): bool
    {
        return $this->investigation_completes_at !== null
            && $this->investigation_completes_at <= now()
            && $this->completed_at === null;
    }

    /**
     * Get the expiration time (48 hours after discovery)
     */
    public function getExpiresAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->created_at === null) {
            return null;
        }

        return $this->created_at->copy()->addHours(48);
    }

    /**
     * Get investigation progress percentage (0-100)
     */
    public function getInvestigationProgress(): float
    {
        if ($this->investigation_started_at === null || $this->investigation_completes_at === null) {
            return 0.0;
        }

        $totalHours = $this->investigation_started_at->diffInHours($this->investigation_completes_at);
        if ($totalHours === 0) {
            return 100.0;
        }

        $hoursRemaining = max(0, now()->diffInHours($this->investigation_completes_at, false));
        $percentage = (($totalHours - $hoursRemaining) / $totalHours) * 100;

        return min($percentage, 100.0);
    }

    /**
     * Get CSS color class based on investigation progress
     */
    public function getProgressColorClass(): string
    {
        $percentage = $this->getInvestigationProgress();

        if ($percentage >= 75) {
            return 'text-green';
        } elseif ($percentage >= 50) {
            return 'text-info';
        } elseif ($percentage >= 25) {
            return 'text-warning';
        } else {
            return 'text-red';
        }
    }

    /**
     * Get ticks remaining until investigation completes
     */
    public function getTicksRemaining(): int
    {
        if ($this->investigation_completes_at === null) {
            return 0;
        }

        return max(0, now()->diffInHours($this->investigation_completes_at, false));
    }

    /**
     * Get ticks remaining until valuable expires
     */
    public function getTicksUntilExpiration(): int
    {
        $expiresAt = $this->getExpiresAt();
        if ($expiresAt === null) {
            return 0;
        }

        return max(0, now()->diffInHours($expiresAt, false));
    }

    /**
     * Check if valuable has expired (48 hours after discovery)
     */
    public function isExpired(): bool
    {
        return $this->getExpiresAt() !== null
            && $this->getExpiresAt() <= now()
            && $this->completed_at === null;
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
     * Scope to active valuables (discovered or investigating, not yet completed)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    /**
     * Scope to successfully stolen valuables that haven't been sold yet
     */
    public function scopeStolen(Builder $query): Builder
    {
        return $query
            ->whereNotNull('completed_at')
            ->where('success', true)
            ->whereNull('sold_at');
    }

    /**
     * Scope to completed valuables (sold or failed)
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->whereNotNull('sold_at')
              ->orWhere(function($q2) {
                  $q2->whereNotNull('completed_at')->where('success', false);
              });
        });
    }
}
