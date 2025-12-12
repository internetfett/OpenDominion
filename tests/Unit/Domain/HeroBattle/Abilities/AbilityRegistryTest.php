<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\AbilityRegistry;
use OpenDominion\Helpers\HeroAbilityHelper;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class AbilityRegistryTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testAbilityRegistryRestoresStateFromCombatant()
    {
        $abilityHelper = new HeroAbilityHelper();
        $registry = new AbilityRegistry($abilityHelper);

        // Create a combatant with abilities and saved state
        $combatant = new HeroCombatant();
        $combatant->abilities = [
            'lifesteal',
            'hardiness',
            'power_strike',
        ];
        $combatant->ability_state = [
            'hardiness' => ['charges' => 0],
            'power_strike' => ['last_used_turn' => 3],
        ];

        // Load abilities - should restore state
        $abilities = $registry->getAbilitiesForCombatant($combatant);

        $this->assertCount(3, $abilities);

        // Check hardiness state
        $hardiness = $abilities->firstWhere('key', 'hardiness');
        $this->assertEquals(0, $hardiness->getCharges());
        $this->assertFalse($hardiness->hasCharges());

        // Check power strike state
        $powerStrike = $abilities->firstWhere('key', 'power_strike');
        $this->assertEquals(3, $powerStrike->getLastUsedTurn());
        $this->assertTrue($powerStrike->isOnCooldown(4));
        $this->assertTrue($powerStrike->isOnCooldown(5));
        $this->assertFalse($powerStrike->isOnCooldown(6));
    }

    public function testAbilityRegistrySavesStateBackToCombatant()
    {
        $abilityHelper = new HeroAbilityHelper();
        $registry = new AbilityRegistry($abilityHelper);

        $combatant = new HeroCombatant();
        $combatant->abilities = ['hardiness', 'power_strike'];
        $combatant->ability_state = [];

        // Load abilities
        $abilities = $registry->getAbilitiesForCombatant($combatant);

        // Modify state
        $hardiness = $abilities->firstWhere('key', 'hardiness');
        $hardiness->consume(); // Use up 1 charge

        $powerStrike = $abilities->firstWhere('key', 'power_strike');
        $powerStrike->markUsed(5);

        // Save state (this would normally be called by HeroBattleService)
        $registry->saveAbilityStates($combatant, $abilities);

        // Verify state was saved to combatant
        $this->assertEquals([
            'hardiness' => ['charges' => 0],
            'power_strike' => ['last_used_turn' => 5],
        ], $combatant->ability_state);
    }

    public function testAbilityRegistryLoadsAbilitiesWithPerCombatantConfig()
    {
        $abilityHelper = new HeroAbilityHelper();
        $registry = new AbilityRegistry($abilityHelper);

        $combatant = new HeroCombatant();
        $combatant->abilities = [
            ['key' => 'lifesteal', 'config' => ['heal_percent' => 1.0]], // 100%
        ];

        $abilities = $registry->getAbilitiesForCombatant($combatant);

        $lifesteal = $abilities->first();
        $this->assertEquals('lifesteal', $lifesteal->getKey());
        $this->assertEquals(1.0, $lifesteal->getConfig()['heal_percent']);
    }

    public function testAbilityRegistryOnlySavesNonEmptyState()
    {
        $abilityHelper = new HeroAbilityHelper();
        $registry = new AbilityRegistry($abilityHelper);

        $combatant = new HeroCombatant();
        $combatant->abilities = ['lifesteal', 'elusive'];

        $abilities = $registry->getAbilitiesForCombatant($combatant);

        // These abilities have no state (no charges, no cooldowns)
        $registry->saveAbilityStates($combatant, $abilities);

        // Should save empty array since no abilities have state
        $this->assertEquals([], $combatant->ability_state);
    }
}
