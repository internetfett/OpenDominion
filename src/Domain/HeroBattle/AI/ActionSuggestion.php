<?php

namespace OpenDominion\Domain\HeroBattle\AI;

/**
 * Action Suggestion
 *
 * Represents a suggested action from an AI strategy or ability.
 * Includes priority for resolving conflicts between multiple suggestions.
 * Supports veto system where abilities can block certain actions.
 */
class ActionSuggestion
{
    // Priority levels
    public const PRIORITY_FORCED = 100;      // Forced by ability (darkness, summon)
    public const PRIORITY_CRITICAL = 75;     // Critical situation (near death, must heal)
    public const PRIORITY_PREFERRED = 50;    // Preferred action (crushing_blow upgrade)
    public const PRIORITY_NORMAL = 25;       // Normal strategy decision
    public const PRIORITY_FALLBACK = 0;      // Fallback option
    public const PRIORITY_VETO = -1;         // Veto an action (abilities can block)

    public string $action;
    public ?int $targetId;
    public int $priority;
    public string $reason;
    public bool $isVeto;

    public function __construct(
        string $action,
        ?int $targetId,
        int $priority,
        string $reason = '',
        bool $isVeto = false
    ) {
        $this->action = $action;
        $this->targetId = $targetId;
        $this->priority = $priority;
        $this->reason = $reason;
        $this->isVeto = $isVeto;
    }

    /**
     * Create a forced action suggestion (highest priority)
     */
    public static function forced(string $action, ?int $targetId = null, string $reason = ''): self
    {
        return new self($action, $targetId, self::PRIORITY_FORCED, $reason);
    }

    /**
     * Create a veto to block an action
     */
    public static function veto(string $action, string $reason = ''): self
    {
        return new self($action, null, self::PRIORITY_VETO, $reason, true);
    }

    /**
     * Create a critical action suggestion
     */
    public static function critical(string $action, ?int $targetId = null, string $reason = ''): self
    {
        return new self($action, $targetId, self::PRIORITY_CRITICAL, $reason);
    }

    /**
     * Create a preferred action suggestion
     */
    public static function preferred(string $action, ?int $targetId = null, string $reason = ''): self
    {
        return new self($action, $targetId, self::PRIORITY_PREFERRED, $reason);
    }

    /**
     * Create a normal action suggestion
     */
    public static function normal(string $action, ?int $targetId = null, string $reason = ''): self
    {
        return new self($action, $targetId, self::PRIORITY_NORMAL, $reason);
    }

    /**
     * Create a fallback action suggestion
     */
    public static function fallback(string $action, ?int $targetId = null, string $reason = ''): self
    {
        return new self($action, $targetId, self::PRIORITY_FALLBACK, $reason);
    }

    /**
     * Convert to array format expected by HeroBattleService
     */
    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'target' => $this->targetId,
        ];
    }
}
