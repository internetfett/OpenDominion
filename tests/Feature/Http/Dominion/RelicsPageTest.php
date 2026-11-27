<?php

namespace OpenDominion\Tests\Feature\Http\Dominion;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\Hero;
use OpenDominion\Models\Race;
use OpenDominion\Models\Relic;
use OpenDominion\Models\Round;
use OpenDominion\Tests\AbstractTestCase;

class RelicsPageTest extends AbstractTestCase
{
    use DatabaseTransactions;

    protected Round $round;

    protected Dominion $dominion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->round = $this->createRound('-7 days');
        $this->dominion = $this->createDominionWithLegacyStats($this->createAndImpersonateUser(), $this->round, Race::where('key', 'human')->firstOrFail());
        $this->selectDominion($this->dominion);
    }

    public function testPageListsInventoryAndRealmListings(): void
    {
        Relic::factory()->ownedBy($this->dominion)->create(['name' => 'Held Idol']);
        Relic::factory()->ownedBy($this->dominion)->sold(5000)->create(['name' => 'Sold Idol']);
        $realmmate = $this->createDominion($this->createUser(), $this->round, null, $this->dominion->realm);
        Relic::factory()->ownedBy($realmmate)->listed()->create(['name' => 'Listed Idol']);

        $this->get(route('dominion.relics'))
            ->assertOk()
            ->assertSee('Held Idol')
            ->assertSee('Listed Idol')
            ->assertDontSee('Sold Idol');
    }

    public function testHistoryListsSoldRelics(): void
    {
        Relic::factory()->ownedBy($this->dominion)->sold(5000)->create(['name' => 'Sold Idol']);

        $this->get(route('dominion.relics.history'))
            ->assertOk()
            ->assertSee('Sold Idol');
    }

    public function testSearchSettingsCanBeSaved(): void
    {
        $this->post(route('dominion.relics.search'), ['intensity' => 2, 'stop_on_find' => 0])
            ->assertRedirect(route('dominion.relics'));

        $this->assertEquals(2, $this->dominion->fresh()->getSetting('relics.search_intensity'));
    }

    public function testSearchRejectsInvalidIntensity(): void
    {
        $this->post(route('dominion.relics.search'), ['intensity' => 7])
            ->assertSessionHasErrors('intensity');

        $this->assertNull($this->dominion->fresh()->getSetting('relics.search_intensity'));
    }

    public function testSellingFromThePage(): void
    {
        $relic = Relic::factory()->ownedBy($this->dominion)->create();

        $this->post(route('dominion.relics.sell', $relic->id))
            ->assertRedirect(route('dominion.relics'));

        $this->assertNotNull($relic->fresh()->sold_at);
    }

    public function testHeroPageListsEquipmentRelics(): void
    {
        Hero::create([
            'dominion_id' => $this->dominion->id,
            'name' => 'Relic Hero',
            'class' => 'blacksmith',
            'experience' => 1200,
        ]);
        Relic::factory()->ownedBy($this->dominion)->equipment('lacquered_hardwood_wand', 'common')->create(['name' => 'Lacquered Hardwood Wand']);

        $this->get(route('dominion.heroes'))
            ->assertOk()
            ->assertSee('Lacquered Hardwood Wand');
    }
}
