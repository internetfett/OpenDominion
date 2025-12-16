<?php

namespace OpenDominion\Tests\Unit\Domain\HeroBattle\AI;

use Illuminate\Support\Collection;
use Mockery as m;
use OpenDominion\Domain\HeroBattle\AI\ActionSuggestion;
use OpenDominion\Domain\HeroBattle\AI\CombatAI;
use OpenDominion\Domain\HeroBattle\AI\StrategyInterface;
use OpenDominion\Domain\HeroBattle\Abilities\AbstractAbility;
use OpenDominion\Domain\HeroBattle\Abilities\Traits\InfluencesDecisions;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class CombatAITest extends AbstractBrowserKitTestCase
{

    public function testForcedActionTakesPrecedenceOverNormal()
    {
        // Create mock ability that forces an action
        $forcedAbility = new class extends AbstractAbility implements InfluencesDecisions {
            public function suggestActions(
                HeroCombatant $combatant,
                HeroBattle $battle,
                Collection $livingCombatants
            ): Collection {
                return collect([
                    ActionSuggestion::forced('darkness', null, 'Forced action test')
                ]);
            }
        };

        // Create mock strategy that suggests normal action
        $strategy = new class implements StrategyInterface {
            public function suggestActions(
                HeroCombatant $combatant,
                HeroBattle $battle,
                Collection $livingCombatants,
                Collection $abilities
            ): Collection {
                return collect([
                    ActionSuggestion::normal('attack', null, 'Normal action test')
                ]);
            }

            public function getName(): string
            {
                return 'test_strategy';
            }
        };

        $ai = new CombatAI($strategy, collect([$forcedAbility]), collect());

        $battle = m::mock(HeroBattle::class);
        $battle->current_turn = 1;

        $combatant = m::mock(HeroCombatant::class);
        $combatant->abilities = [];
        $combatant->last_action = null;

        $result = $ai->determineAction($combatant, $battle, collect([$combatant]));

        $this->assertEquals('darkness', $result['action']);
        $this->assertNull($result['target']);
    }

    public function testVetoSystemBlocksActions()
    {
        // Create mock ability that vetoes 'attack'
        $vetoAbility = new class extends AbstractAbility implements InfluencesDecisions {
            public function suggestActions(
                HeroCombatant $combatant,
                HeroBattle $battle,
                Collection $livingCombatants
            ): Collection {
                return collect([
                    ActionSuggestion::veto('attack', 'Veto test'),
                    ActionSuggestion::preferred('defend', null, 'Fallback action')
                ]);
            }
        };

        // Create mock strategy that suggests 'attack'
        $strategy = new class implements StrategyInterface {
            public function suggestActions(
                HeroCombatant $combatant,
                HeroBattle $battle,
                Collection $livingCombatants,
                Collection $abilities
            ): Collection {
                return collect([
                    ActionSuggestion::normal('attack', null, 'Normal attack'),
                ]);
            }

            public function getName(): string
            {
                return 'test_strategy';
            }
        };

        $ai = new CombatAI($strategy, collect([$vetoAbility]), collect());

        $battle = m::mock(HeroBattle::class);
        $battle->current_turn = 1;

        $combatant = m::mock(HeroCombatant::class);
        $combatant->abilities = [];
        $combatant->last_action = null;

        $result = $ai->determineAction($combatant, $battle, collect([$combatant]));

        // Attack should be vetoed, defend should be selected
        $this->assertEquals('defend', $result['action']);
    }

    public function testLimitedActionConstraint()
    {
        // Create mock strategy that suggests 'focus'
        $strategy = new class implements StrategyInterface {
            public function suggestActions(
                HeroCombatant $combatant,
                HeroBattle $battle,
                Collection $livingCombatants,
                Collection $abilities
            ): Collection {
                return collect([
                    ActionSuggestion::normal('focus', null, 'Focus action'),
                    ActionSuggestion::fallback('attack', null, 'Fallback attack'),
                ]);
            }

            public function getName(): string
            {
                return 'test_strategy';
            }
        };

        $limitedActions = collect(['focus', 'counter']);
        $ai = new CombatAI($strategy, collect(), $limitedActions);

        $battle = m::mock(HeroBattle::class);
        $battle->current_turn = 1;

        $combatant = m::mock(HeroCombatant::class);
        $combatant->abilities = [];
        $combatant->last_action = 'focus'; // Last action was focus

        $result = $ai->determineAction($combatant, $battle, collect([$combatant]));

        // Focus should be blocked because it was the last action and is limited
        // Should fall back to 'attack'
        $this->assertEquals('attack', $result['action']);
    }

    public function testActionTransformation()
    {
        // Create mock strategy that suggests 'attack'
        $strategy = new class implements StrategyInterface {
            public function suggestActions(
                HeroCombatant $combatant,
                HeroBattle $battle,
                Collection $livingCombatants,
                Collection $abilities
            ): Collection {
                return collect([
                    ActionSuggestion::normal('attack', null, 'Normal attack'),
                ]);
            }

            public function getName(): string
            {
                return 'test_strategy';
            }
        };

        $ai = new CombatAI($strategy, collect(), collect());

        $battle = m::mock(HeroBattle::class);
        $battle->current_turn = 1;

        $combatant = m::mock(HeroCombatant::class);
        $combatant->abilities = ['crushing_blow']; // Has crushing_blow ability
        $combatant->last_action = null;

        $result = $ai->determineAction($combatant, $battle, collect([$combatant]));

        // Attack should be transformed to crushing_blow
        $this->assertEquals('crushing_blow', $result['action']);
    }
}
