<?php

namespace OpenDominion\HeroCombat\Presentation;

use OpenDominion\HeroCombat\Engine\ActionValidator;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Effects\EffectKind;
use OpenDominion\HeroCombat\Engine\Stats\Stat;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;

/**
 * Read-only view model of a battle from one hero's point of view.
 */
class BattleView
{
    private ActionValidator $validator;

    /**
     * The combatant that represents the viewer (their own hero, else the first one they pilot).
     */
    public readonly ?CombatantState $viewer;

    /**
     * @param CombatantState[] $controlled combatants the viewing dominion pilots
     */
    public function __construct(
        public readonly HeroBattle $model,
        public readonly Battle $battle,
        public readonly array $controlled = [],
        ?int $viewerDominionId = null,
    ) {
        $this->validator = new ActionValidator($battle);

        $own = array_filter($controlled, fn (CombatantState $c) => $c->dominionId === $viewerDominionId);
        $this->viewer = reset($own) ?: (reset($controlled) ?: null);
    }

    /**
     * The viewer's team first, then everyone else grouped by team.
     *
     * @return array<int, array{label: string, combatants: CombatantState[]}>
     */
    public function sides(): array
    {
        $teams = [];
        foreach ($this->battle->combatants() as $combatant) {
            $teams[$combatant->team][] = $combatant;
        }

        $viewerTeam = $this->viewer?->team;
        uksort($teams, fn ($a, $b) => [$a !== $viewerTeam, $a] <=> [$b !== $viewerTeam, $b]);

        $sides = [];
        foreach ($teams as $team => $combatants) {
            $sides[] = [
                'label' => $team === $viewerTeam ? 'Your side' : (count($teams) > 2 ? "Team {$team}" : 'Opponents'),
                'combatants' => $combatants,
            ];
        }

        return $sides;
    }

    public function isControlled(CombatantState $combatant): bool
    {
        foreach ($this->controlled as $controlled) {
            if ($controlled->id === $combatant->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * A combatant the viewer pilots but does not own.
     */
    public function isBorrowed(CombatantState $combatant): bool
    {
        return $this->isControlled($combatant) && $this->viewer !== null && $combatant->dominionId !== $this->viewer->dominionId;
    }

    /**
     * Piloted combatants that still need orders, the viewer's own hero first.
     *
     * @return CombatantState[]
     */
    public function commandable(): array
    {
        if ($this->model->finished) {
            return [];
        }

        $living = array_values(array_filter($this->controlled, fn (CombatantState $c) => $c->isAlive()));
        usort($living, fn (CombatantState $a, CombatantState $b) => [$this->isBorrowed($a), $a->id] <=> [$this->isBorrowed($b), $b->id]);

        return $living;
    }

    public function model(CombatantState $combatant): ?HeroCombatant
    {
        return $this->model->combatants->firstWhere('id', $combatant->id);
    }

    /**
     * @return array<string, array{label: string, base: int, value: int, tooltip: string}>
     */
    public function stats(CombatantState $combatant): array
    {
        $tooltips = [
            'health' => 'Current and maximum health',
            'attack' => 'Attack damage, reduced by defense of opponent',
            'defense' => 'Reduce incoming attack damage by this amount, doubled while defending',
            'evasion' => 'Chance to evade an attack is equal to this percentage',
            'focus' => 'Focus increases attack damage by this amount',
            'counter' => 'Counter attack damage is increased by this amount',
            'recover' => 'Heal damage equal to this amount',
        ];

        $stats = [];
        foreach (Stat::cases() as $stat) {
            $stats[$stat->value] = [
                'label' => $stat->label(),
                'base' => $combatant->baseStat($stat),
                'value' => $this->battle->stat($combatant, $stat),
                'tooltip' => $tooltips[$stat->value],
            ];
        }

        return $stats;
    }

    /**
     * Visible effects applying to the combatant, from every scope.
     *
     * @return array<int, array{key: string, name: string, description: string, kind: string, turns: int|null, stacks: int}>
     */
    public function effects(CombatantState $combatant): array
    {
        $effects = [];
        foreach ($this->battle->effects->applicableTo($combatant) as [$effect, $instance]) {
            if (!$effect->visible()) {
                continue;
            }
            $effects[] = [
                'key' => $effect->key(),
                'name' => $effect->name(),
                'description' => $effect->description($instance),
                'kind' => $effect->kind()->value,
                'turns' => $instance->remainingTurns,
                'stacks' => $instance->stacks,
            ];
        }

        usort($effects, fn ($a, $b) => [$a['kind'] === EffectKind::Innate->value, $a['name']] <=> [$b['kind'] === EffectKind::Innate->value, $b['name']]);

        return $effects;
    }

    /**
     * Damage the combatant's shield will still absorb.
     */
    public function shield(CombatantState $combatant): int
    {
        return (int) ($this->battle->effects->find($combatant, 'shield')?->data['pool'] ?? 0);
    }

    public function canAct(CombatantState $combatant): bool
    {
        $model = $this->model($combatant);

        return $model !== null && $this->isControlled($combatant) && !$this->model->finished && $combatant->isAlive() && $model->time_bank > 0;
    }

    /**
     * A piloted combatant's action menu.
     *
     * @return array<int, array{key: string, name: string, description: string, usable: bool, needsTarget: bool, hostile: bool, cooldown: int, charges: int|null}>
     */
    public function abilities(CombatantState $combatant): array
    {
        $abilities = [];
        foreach ($combatant->abilities as $key) {
            if (!$this->battle->registry->hasAbility($key)) {
                continue;
            }
            $ability = $this->battle->registry->ability($key);
            if (!$ability->selectable()) {
                continue;
            }

            $abilities[] = [
                'key' => $key,
                'name' => $ability->name(),
                'description' => $ability->description(),
                'usable' => $this->canAct($combatant) && $this->validator->canQueue($combatant, $key),
                'needsTarget' => $ability->targetRule()->needsChosenTarget(),
                'hostile' => $ability->targetRule()->isHostile(),
                'cooldown' => $this->validator->turnsUntilReady($combatant, $key),
                'charges' => $this->validator->chargesRemaining($combatant, $key),
            ];
        }

        return $abilities;
    }

    /**
     * Valid targets a piloted combatant could pick for an ability.
     *
     * @return CombatantState[]
     */
    public function targetsFor(CombatantState $combatant, string $abilityKey): array
    {
        $ability = $this->battle->registry->ability($abilityKey);

        return array_values(array_filter(
            $this->battle->living(),
            fn (CombatantState $c) => $this->battle->targets->isValidChoice($combatant, $ability, $c),
        ));
    }

    /**
     * @return array<int, array{turn: int, ability: string, target: string|null}>
     */
    public function queue(CombatantState $combatant): array
    {
        $queue = [];
        foreach ($combatant->queue as $index => $queued) {
            $queue[] = [
                'turn' => $this->battle->turn() + $index,
                'ability' => $this->abilityName($queued['ability']),
                'target' => $this->battle->combatant($queued['target'] ?? null)?->name,
            ];
        }

        return $queue;
    }

    public function abilityName(string $key): string
    {
        return $this->battle->registry->hasAbility($key) ? $this->battle->registry->ability($key)->name() : ucwords(str_replace('_', ' ', $key));
    }

    /**
     * @return array<string, string> key => name
     */
    public function strategies(): array
    {
        $strategies = [];
        foreach ($this->battle->registry->strategies() as $key => $strategy) {
            if ($strategy->playerSelectable()) {
                $strategies[$key] = $strategy->name();
            }
        }

        return $strategies;
    }
}
