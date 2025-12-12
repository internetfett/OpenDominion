<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Special\UndyingLegion;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class UndyingLegionTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testUndyingLegionSetsDefenseTo999WhenMinionsAlive()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        $boss = new HeroCombatant();
        $boss->hero_battle_id = $battle->id;
        $boss->name = 'Boss';
        $boss->defense = 50;
        $boss->current_health = 100;
        $boss->save();

        $minion = new HeroCombatant();
        $minion->hero_battle_id = $battle->id;
        $minion->name = 'Minion';
        $minion->defense = 20;
        $minion->current_health = 30;
        $minion->save();

        // Refresh battle to load combatants relationship
        $battle->load('combatants');
        $boss->battle = $battle;

        $undyingLegion = new UndyingLegion('undying_legion', []);
        $modifiedDefense = $undyingLegion->modifyDefense($boss, $boss->defense);

        $this->assertEquals(999, $modifiedDefense); // Protected by minions
    }

    public function testUndyingLegionDoesNotTriggerWhenNoMinionsAlive()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        $boss = new HeroCombatant();
        $boss->hero_battle_id = $battle->id;
        $boss->name = 'Boss';
        $boss->defense = 50;
        $boss->current_health = 100;
        $boss->save();

        $deadMinion = new HeroCombatant();
        $deadMinion->hero_battle_id = $battle->id;
        $deadMinion->name = 'Dead Minion';
        $deadMinion->defense = 20;
        $deadMinion->current_health = 0; // Dead
        $deadMinion->save();

        $battle->load('combatants');
        $boss->battle = $battle;

        $undyingLegion = new UndyingLegion('undying_legion', []);
        $modifiedDefense = $undyingLegion->modifyDefense($boss, $boss->defense);

        $this->assertEquals(50, $modifiedDefense); // No living minions, normal defense
    }

    public function testUndyingLegionDoesNotTriggerWhenAlone()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        // Boss is alone in the battle
        $boss = new HeroCombatant();
        $boss->hero_battle_id = $battle->id;
        $boss->name = 'Boss';
        $boss->defense = 50;
        $boss->current_health = 100;
        $boss->save();

        $battle->load('combatants');
        $boss->battle = $battle;

        $undyingLegion = new UndyingLegion('undying_legion', []);
        $modifiedDefense = $undyingLegion->modifyDefense($boss, $boss->defense);

        $this->assertEquals(50, $modifiedDefense); // No minions, normal defense
    }

    public function testUndyingLegionWorksWithMultipleMinions()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        $boss = new HeroCombatant();
        $boss->hero_battle_id = $battle->id;
        $boss->name = 'Boss';
        $boss->defense = 50;
        $boss->current_health = 100;
        $boss->save();

        // Create 3 minions
        for ($i = 1; $i <= 3; $i++) {
            $minion = new HeroCombatant();
            $minion->hero_battle_id = $battle->id;
            $minion->name = "Minion {$i}";
            $minion->defense = 20;
            $minion->current_health = 30;
            $minion->save();
        }

        $battle->load('combatants');
        $boss->battle = $battle;

        $undyingLegion = new UndyingLegion('undying_legion', []);
        $modifiedDefense = $undyingLegion->modifyDefense($boss, $boss->defense);

        $this->assertEquals(999, $modifiedDefense); // Protected by multiple minions
    }

    public function testUndyingLegionTransitionsWhenLastMinionDies()
    {
        $battle = HeroBattle::create(['current_turn' => 1]);

        $boss = new HeroCombatant();
        $boss->hero_battle_id = $battle->id;
        $boss->name = 'Boss';
        $boss->defense = 50;
        $boss->current_health = 100;
        $boss->save();

        $minion = new HeroCombatant();
        $minion->hero_battle_id = $battle->id;
        $minion->name = 'Minion';
        $minion->defense = 20;
        $minion->current_health = 30;
        $minion->save();

        $battle->load('combatants');
        $boss->battle = $battle;

        $undyingLegion = new UndyingLegion('undying_legion', []);

        // Defense should be 999 when minion is alive
        $modifiedDefense = $undyingLegion->modifyDefense($boss, $boss->defense);
        $this->assertEquals(999, $modifiedDefense);

        // Kill the minion
        $minion->current_health = 0;
        $minion->save();
        $battle->load('combatants'); // Refresh

        // Defense should return to normal
        $modifiedDefense = $undyingLegion->modifyDefense($boss, $boss->defense);
        $this->assertEquals(50, $modifiedDefense);
    }
}
