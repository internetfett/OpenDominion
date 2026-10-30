<?php

namespace OpenDominion\HeroCombat\Analysis;

use OpenDominion\HeroCombat\Contracts\AiStrategy;
use OpenDominion\HeroCombat\Engine\ActionValidator;
use OpenDominion\HeroCombat\Engine\Battle;
use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Engine\Intent;

/**
 * Game-theory strategy for 1v1 basic-ability battles, used for offline analysis only (it is
 * not registered in the game). Each turn it previews every pairing of both sides' legal
 * actions, values the outcomes with its profile, solves the resulting matrix game for a
 * mixed strategy and samples from it.
 *
 * It reads only public state: TurnPreview works from a snapshot without queues or intents.
 */
final class TacticianStrategy implements AiStrategy
{
    public function __construct(
        private TacticianProfile $profile,
        private TurnPreview $preview = new TurnPreview(),
        private MatrixGameSolver $solver = new MatrixGameSolver(),
    ) {
    }

    public function key(): string
    {
        return 'tactician:' . $this->profile->key;
    }

    public function name(): string
    {
        return 'Tactician (' . ucfirst($this->profile->key) . ')';
    }

    public function playerSelectable(): bool
    {
        return false;
    }

    public function chooseIntent(CombatantState $actor, Battle $battle): Intent
    {
        $opponent = $battle->enemiesOf($actor)[0] ?? null;
        $myActions = $this->legalActions($actor, $battle);

        if ($opponent === null || count($myActions) <= 1) {
            return $this->intent($actor, $myActions[0] ?? ActionValidator::PASS, $opponent, $battle);
        }

        $mix = $this->mix($actor, $opponent, $battle);
        $choice = $battle->random->weighted(array_combine($myActions, array_map(fn ($p) => max(0.0, $p) * 1000, $mix)));

        return $this->intent($actor, $choice, $opponent, $battle);
    }

    /**
     * The probability this strategy assigns to each of its legal actions this turn.
     *
     * @return array<int, float> in the order of legalActions()
     */
    public function mix(CombatantState $actor, CombatantState $opponent, Battle $battle): array
    {
        $myActions = $this->legalActions($actor, $battle);
        $theirActions = $this->legalActions($opponent, $battle);

        $payoffs = [];
        foreach ($myActions as $r => $mine) {
            foreach ($theirActions as $c => $theirs) {
                $outcome = $this->preview->outcome($battle, [$actor->id => $mine, $opponent->id => $theirs]);
                $payoffs[$r][$c] = $this->value($actor, $opponent, $battle, $outcome);
            }
        }

        $mix = $this->solver->solve($payoffs);
        $uniform = 1 / count($myActions);

        return array_map(fn ($p) => (1 - $this->profile->temperature) * $p + $this->profile->temperature * $uniform, $mix);
    }

    /**
     * @return string[] abilities the combatant can use right now
     */
    public function legalActions(CombatantState $combatant, Battle $battle): array
    {
        $validator = new ActionValidator($battle);

        return array_values(array_filter(
            $combatant->abilities,
            fn (string $ability) => $validator->canPerform($combatant, $ability),
        ));
    }

    private function value(CombatantState $me, CombatantState $them, Battle $battle, TurnOutcome $outcome): float
    {
        $profile = $this->profile;

        $myChange = $outcome->health[$me->id] - $me->currentHealth;
        $theirLoss = $them->currentHealth - $outcome->health[$them->id];

        $value = $profile->damageDealt * $theirLoss;
        $value += $myChange >= 0 ? $profile->healing * $myChange : $profile->damageTaken * $myChange;

        $mySetup = $outcome->offense[$me->id] - TurnPreview::offense($battle, $me);
        $theirSetup = $outcome->offense[$them->id] - TurnPreview::offense($battle, $them);
        $value += $profile->setup * ($mySetup - $theirSetup);

        $iWin = $outcome->deathChance[$them->id] * (1 - $outcome->deathChance[$me->id]);
        $iLose = $outcome->deathChance[$me->id] * (1 - $outcome->deathChance[$them->id]);
        $value += $profile->victory * ($iWin - $iLose);

        return $value;
    }

    private function intent(CombatantState $actor, string $abilityKey, ?CombatantState $opponent, Battle $battle): Intent
    {
        $rule = $battle->registry->ability($abilityKey)->targetRule();
        $target = $rule->needsChosenTarget() && $rule->isHostile() ? $opponent?->id : null;

        return new Intent($actor->id, $abilityKey, $target, Intent::SOURCE_AI);
    }
}
