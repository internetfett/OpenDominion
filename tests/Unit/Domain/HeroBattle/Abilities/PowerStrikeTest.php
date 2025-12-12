<?php

namespace Tests\Unit\Domain\HeroBattle\Abilities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenDominion\Domain\HeroBattle\Abilities\Active\PowerStrike;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;
use OpenDominion\Models\HeroBattle;
use OpenDominion\Models\HeroCombatant;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class PowerStrikeTest extends AbstractBrowserKitTestCase
{
    use RefreshDatabase;

    public function testPowerStrikeHasCooldown()
    {
        $ability = new PowerStrike('power_strike', [
            'bonus_damage' => 20,
            'cooldown' => 3,
        ]);

        // Mark as used on turn 1
        $ability->markUsed(1);

        // Check cooldown status
        $this->assertTrue($ability->isOnCooldown(1)); // Same turn
        $this->assertTrue($ability->isOnCooldown(2)); // 1 turn later
        $this->assertTrue($ability->isOnCooldown(3)); // 2 turns later
        $this->assertFalse($ability->isOnCooldown(4)); // 3 turns later - ready!
        $this->assertFalse($ability->isOnCooldown(5)); // 4 turns later - still ready
    }

    public function testPowerStrikeStateCanBeSavedAndRestored()
    {
        // Create ability and use it on turn 5
        $ability = new PowerStrike('power_strike', [
            'bonus_damage' => 20,
            'cooldown' => 3,
        ]);

        $ability->markUsed(5);

        // Get state for saving
        $state = $ability->getState();
        $this->assertEquals(['last_used_turn' => 5], $state);

        // Create new ability instance and restore state
        $newAbility = new PowerStrike('power_strike', [
            'bonus_damage' => 20,
            'cooldown' => 3,
        ]);

        $newAbility->restoreState($state);

        // Verify state was restored correctly
        $this->assertEquals(5, $newAbility->getLastUsedTurn());
        $this->assertTrue($newAbility->isOnCooldown(6));
        $this->assertTrue($newAbility->isOnCooldown(7));
        $this->assertFalse($newAbility->isOnCooldown(8));
    }

    public function testPowerStrikeWithoutCooldownIsAlwaysReady()
    {
        // Ability with no cooldown configured
        $ability = new PowerStrike('power_strike', [
            'bonus_damage' => 20,
        ]);

        $ability->markUsed(1);

        // Should always be available
        $this->assertFalse($ability->isOnCooldown(1));
        $this->assertFalse($ability->isOnCooldown(2));
        $this->assertFalse($ability->isOnCooldown(100));
    }

    public function testPowerStrikeGetStateOnlyReturnsNonNullValues()
    {
        // Fresh ability with no state
        $ability = new PowerStrike('power_strike', [
            'bonus_damage' => 20,
            'cooldown' => 3,
        ]);

        $state = $ability->getState();
        $this->assertEmpty($state);

        // After being used
        $ability->markUsed(10);
        $state = $ability->getState();
        $this->assertEquals(['last_used_turn' => 10], $state);
    }

    public function testAbilityWithChargesAndCooldownBothSaved()
    {
        // Create an ability with both charges and cooldown
        $ability = new PowerStrike('power_strike', [
            'bonus_damage' => 20,
            'cooldown' => 3,
            'charges' => 5,
        ]);

        $ability->markUsed(2);
        $ability->consume();
        $ability->consume();

        $state = $ability->getState();
        $this->assertEquals([
            'charges' => 3,
            'last_used_turn' => 2,
        ], $state);

        // Restore to new instance
        $newAbility = new PowerStrike('power_strike', [
            'bonus_damage' => 20,
            'cooldown' => 3,
            'charges' => 5,
        ]);

        $newAbility->restoreState($state);

        $this->assertEquals(3, $newAbility->getCharges());
        $this->assertEquals(2, $newAbility->getLastUsedTurn());
        $this->assertTrue($newAbility->isOnCooldown(3));
    }
}
