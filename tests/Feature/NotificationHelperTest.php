<?php

namespace OpenDominion\Tests\Feature;

use Mockery;
use OpenDominion\Helpers\NotificationHelper;
use OpenDominion\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class NotificationHelperTest extends AbstractTestCase
{
    /** @var NotificationHelper */
    protected $notificationHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationHelper = app(NotificationHelper::class);
    }

    /**
     * The header dropdown calls getNotificationCategories() twice per unread
     * notification, and each build evaluates twelve route() lookups. Asserting
     * the category builders run exactly once across repeated calls is what
     * keeps that cost from scaling with the notification count again.
     */
    public function testNotificationCategoriesAreBuiltOnlyOnce(): void
    {
        /** @var NotificationHelper|Mockery\MockInterface $notificationHelper */
        $notificationHelper = Mockery::mock(NotificationHelper::class)->makePartial();

        $notificationHelper->shouldReceive('getGeneralTypes')->once()->andReturn([]);
        $notificationHelper->shouldReceive('getHourlyDominionTypes')->once()->andReturn([]);
        $notificationHelper->shouldReceive('getIrregularDominionTypes')->once()->andReturn([]);
        $notificationHelper->shouldReceive('getIrregularRealmTypes')->once()->andReturn([]);

        $notificationHelper->getNotificationCategories();
        $notificationHelper->getNotificationCategories();

        $this->assertTrue(true, 'Mockery verifies each category builder ran exactly once.');
    }

    public function testNotificationCategoriesReturnsEveryCategory(): void
    {
        $categories = $this->notificationHelper->getNotificationCategories();

        $this->assertSame(
            ['general', 'hourly_dominion', 'irregular_dominion', 'irregular_realm'],
            array_keys($categories)
        );
    }

    public function testRepeatedCallsReturnIdenticalCategories(): void
    {
        $this->assertSame(
            $this->notificationHelper->getNotificationCategories(),
            $this->notificationHelper->getNotificationCategories()
        );
    }

    /**
     * Guards the memoized payload itself: the cached array still has to carry
     * the resolved route URLs the dropdown links to, not unevaluated closures
     * or nulls.
     */
    public function testCachedCategoriesRetainResolvedRoutes(): void
    {
        $categories = $this->notificationHelper->getNotificationCategories();

        $this->assertSame(
            route('dominion.explore'),
            $categories['hourly_dominion']['exploration_completed']['route']
        );
        $this->assertSame(
            route('dominion.military'),
            $categories['hourly_dominion']['training_completed']['route']
        );
    }

    /**
     * getDefaultUserNotificationSettings() reads through the same cache, so a
     * broken memoization would surface here as missing defaults.
     */
    public function testDefaultUserNotificationSettingsStillResolve(): void
    {
        $settings = $this->notificationHelper->getDefaultUserNotificationSettings();

        $this->assertSame(
            ['general', 'hourly_dominion', 'irregular_dominion', 'irregular_realm'],
            array_keys($settings)
        );
        $this->assertSame(
            ['email' => false, 'ingame' => true],
            $settings['hourly_dominion']['exploration_completed']
        );
    }

    public static function relicNotificationProvider(): array
    {
        return [
            'search find' => ['hourly_dominion', 'relic_found', ['relicName' => 'Ashen Idol', 'rarity' => 'rare', 'category' => 'artifact', 'searchStopped' => true]],
            'max value' => ['hourly_dominion', 'relic_max_value', ['relicNames' => ['Ashen Idol', 'Pale Ring']]],
            'purchased' => ['irregular_dominion', 'relic_purchased', ['buyerDominionId' => null, 'relicName' => 'Ashen Idol', 'price' => 25000]],
            'stolen, thief hidden' => ['irregular_dominion', 'relic_stolen', ['sourceDominionId' => null, 'relicName' => 'Ashen Idol', 'rarity' => 'rare']],
            'theft repelled' => ['irregular_dominion', 'repelled_relic_theft', ['sourceDominionId' => null, 'unitsKilled' => '5 spies']],
            'break-in' => ['irregular_dominion', 'relic_break_in', ['sourceDominionId' => null]],
        ];
    }

    #[DataProvider('relicNotificationProvider')]
    public function testRelicNotificationsRenderAndAreRegistered(string $category, string $type, array $data): void
    {
        $this->assertArrayHasKey($type, $this->notificationHelper->getNotificationCategories()[$category]);
        $this->assertNotEmpty($this->notificationHelper->getNotificationMessage($category, $type, $data));
    }
}
