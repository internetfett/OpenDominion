<?php

namespace OpenDominion\Tests\Unit\Helpers;

use OpenDominion\Helpers\HeroHelper;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class HeroHelperTest extends AbstractBrowserKitTestCase
{
    /** @var HeroHelper */
    protected $heroHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heroHelper = $this->app->make(HeroHelper::class);
    }

    public function testOnlyTechniquesAbilitiesAndGearAreHeroBattleUpgrades(): void
    {
        $this->assertTrue($this->heroHelper->isHeroBattleUpgradeType('technique'));
        $this->assertTrue($this->heroHelper->isHeroBattleUpgradeType('ability'));
        $this->assertTrue($this->heroHelper->isHeroBattleUpgradeType('gear'));
        $this->assertFalse($this->heroHelper->isHeroBattleUpgradeType('discipline'));
        $this->assertFalse($this->heroHelper->isHeroBattleUpgradeType('combo'));
        $this->assertFalse($this->heroHelper->isHeroBattleUpgradeType('doctrine'));
        $this->assertFalse($this->heroHelper->isHeroBattleUpgradeType('item'));
        $this->assertFalse($this->heroHelper->isHeroBattleUpgradeType('magic'));
        $this->assertFalse($this->heroHelper->isHeroBattleUpgradeType('directive'));
    }

    public function testUpgradeTypesAreGroupedBySlotLevel(): void
    {
        $types = $this->heroHelper->getUpgradeTypesByLevel('blacksmith');

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 9], array_keys($types));
        $this->assertEquals(['doctrine'], $types[1]);
        $this->assertEquals(['item', 'magic'], $types[2]);
        $this->assertEquals(['technique'], $types[3]);
        $this->assertEquals(['technique'], $types[5]);
        $this->assertEquals(['ability'], $types[7]);
        $this->assertEquals(['gear'], $types[9]);
    }

    public function testDirectivesDoNotAppearAsSlotTypes(): void
    {
        $types = $this->heroHelper->getUpgradeTypesByLevel('scion');

        foreach ($types as $levelTypes) {
            $this->assertNotContains('directive', $levelTypes);
        }
    }
}
