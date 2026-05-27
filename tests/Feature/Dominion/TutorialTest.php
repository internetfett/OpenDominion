<?php

namespace OpenDominion\Tests\Feature\Dominion;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OpenDominion\Helpers\TutorialHelper;
use OpenDominion\Http\Middleware\PreventRequestForgery;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\User;
use OpenDominion\Tests\AbstractBrowserKitTestCase;

class TutorialTest extends AbstractBrowserKitTestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Dominion $dominion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createAndImpersonateUser();
        $round = $this->createRound();
        $this->dominion = $this->createAndSelectDominionWithLegacyStats($this->user, $round);

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function testPostBranchPersistsExplorerChoice(): void
    {
        $this->post(route('dominion.tutorial.branch', TutorialHelper::BRANCH_EXPLORER));

        $progress = $this->user->fresh()->getTutorialProgress();
        $this->assertSame(TutorialHelper::BRANCH_EXPLORER, $progress['branch']);
        $this->assertContains('choose_branch', $progress['completed']);
    }

    public function testPostSkipMarksSkippableStep(): void
    {
        $this->post(route('dominion.tutorial.skip', 'join_discord'));

        $progress = $this->user->fresh()->getTutorialProgress();
        $this->assertContains('join_discord', $progress['skipped']);
    }

    public function testPostSkipNoOpsOnNonSkippableStep(): void
    {
        $this->post(route('dominion.tutorial.skip', 'protection_build_homes'));

        $progress = $this->user->fresh()->getTutorialProgress();
        $this->assertNotContains('protection_build_homes', $progress['skipped']);
    }

    public function testPostCompleteMarksManualStep(): void
    {
        $this->post(route('dominion.tutorial.complete', 'welcome'));

        $progress = $this->user->fresh()->getTutorialProgress();
        $this->assertContains('welcome', $progress['completed']);
    }

    public function testPostCompleteRejectsAutoStep(): void
    {
        $this->post(route('dominion.tutorial.complete', 'protection_build_homes'));

        $progress = $this->user->fresh()->getTutorialProgress();
        $this->assertNotContains('protection_build_homes', $progress['completed']);
    }
}
