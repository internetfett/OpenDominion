<?php

namespace OpenDominion\Tests\Unit\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OpenDominion\Helpers\TutorialHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\User;
use OpenDominion\Services\User\TutorialService;
use OpenDominion\Tests\AbstractTestCase;

class TutorialServiceTest extends AbstractTestCase
{
    use DatabaseTransactions;

    protected TutorialService $service;
    protected User $user;
    protected Dominion $dominion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TutorialService::class);
        $this->user = $this->createUser();
        $round = $this->createRound();

        // Sets protection_finished = true so the dominion is in the 'active' phase,
        // which makes all phases of step candidates available.
        $this->dominion = $this->createDominionWithLegacyStats($this->user, $round);
    }

    public function testUntouchedUserHasEmptyProgress(): void
    {
        $progress = $this->user->getTutorialProgress();

        $this->assertNull($progress['branch']);
        $this->assertSame([], $progress['completed']);
        $this->assertSame([], $progress['skipped']);
        $this->assertFalse($progress['done']);
    }

    public function testEvaluateAndSyncMarksStateBasedStepComplete(): void
    {
        $this->dominion->update(['building_home' => 10]);

        $this->service->evaluateAndSync($this->user, $this->dominion);

        $progress = $this->user->getTutorialProgress();
        $this->assertContains('protection_build_homes', $progress['completed']);
    }

    public function testEvaluateAndSyncAutoSkipsBuildDockForBoatlessRace(): void
    {
        // Spirit's offensive units (slots 1/3/4) all have need_boat=false.
        $spirit = \OpenDominion\Models\Race::where('name', 'Spirit')->firstOrFail();
        $round = $this->createRound();
        $dominion = $this->createDominionWithLegacyStats($this->user, $round, $spirit);

        $this->service->evaluateAndSync($this->user, $dominion->fresh());

        $progress = $this->user->getTutorialProgress();
        $this->assertContains('build_dock', $progress['skipped']);
    }

    public function testSkipHonorsSkippableFlag(): void
    {
        $this->assertTrue($this->service->skip($this->user, 'join_discord'));
        $progress = $this->user->getTutorialProgress();
        $this->assertContains('join_discord', $progress['skipped']);
    }

    public function testSkipRejectsNonSkippableStep(): void
    {
        $this->assertFalse($this->service->skip($this->user, 'protection_build_homes'));
        $progress = $this->user->getTutorialProgress();
        $this->assertNotContains('protection_build_homes', $progress['skipped']);
    }

    public function testMarkCompleteHonorsManualCompleteFlag(): void
    {
        $this->assertTrue($this->service->markComplete($this->user, 'welcome'));
        $progress = $this->user->getTutorialProgress();
        $this->assertContains('welcome', $progress['completed']);
    }

    public function testMarkCompleteRejectsAutoOnlyStep(): void
    {
        // protection_build_homes has manual_complete=false; it should reject manual marks.
        $this->assertFalse($this->service->markComplete($this->user, 'protection_build_homes'));
        $progress = $this->user->getTutorialProgress();
        $this->assertNotContains('protection_build_homes', $progress['completed']);
    }

    public function testMarkCompleteRejectsUnknownStep(): void
    {
        $this->assertFalse($this->service->markComplete($this->user, 'nonexistent_step'));
    }

    public function testChooseBranchPersistsAndCompletesBranchStep(): void
    {
        $this->assertTrue($this->service->chooseBranch($this->user, TutorialHelper::BRANCH_EXPLORER));

        $progress = $this->user->getTutorialProgress();
        $this->assertSame(TutorialHelper::BRANCH_EXPLORER, $progress['branch']);
        $this->assertContains('choose_branch', $progress['completed']);
    }

    public function testChooseBranchRejectsInvalidBranch(): void
    {
        $this->assertFalse($this->service->chooseBranch($this->user, 'pacifist'));
        $progress = $this->user->getTutorialProgress();
        $this->assertNull($progress['branch']);
    }

    public function testGroupCollapsesWhenAMemberIsCompleted(): void
    {
        // Inject completion directly, simulating an auto-completion of join_discord.
        $progress = $this->user->getTutorialProgress();
        $progress['completed'][] = 'join_discord';
        $progress['branch'] = TutorialHelper::BRANCH_EXPLORER;
        $this->user->setTutorialProgress($progress);
        $this->user->save();

        $state = $this->service->getState($this->user, $this->dominion);

        $surfacedIds = array_merge(
            $state['current'] !== null ? [$state['current']['id']] : [],
            array_keys($state['upcoming']),
        );

        $this->assertNotContains('post_in_council', $surfacedIds);
    }

    public function testSkippingDiscordSurfacesCouncilAsNextGroupMember(): void
    {
        $this->service->chooseBranch($this->user, TutorialHelper::BRANCH_EXPLORER);
        $this->service->skip($this->user, 'join_discord');

        $state = $this->service->getState($this->user, $this->dominion);

        $surfacedIds = array_merge(
            $state['current'] !== null ? [$state['current']['id']] : [],
            array_keys($state['upcoming']),
        );

        $this->assertContains('post_in_council', $surfacedIds);
    }

    public function testDoneFlagStaysFalseWithoutBranch(): void
    {
        // Mark every step complete by brute force without picking a branch.
        $helper = app(TutorialHelper::class);
        $progress = $this->user->getTutorialProgress();
        $progress['completed'] = array_keys($helper->getSteps());
        $this->user->setTutorialProgress($progress);
        $this->user->save();

        $this->service->evaluateAndSync($this->user, $this->dominion);

        $this->assertFalse($this->user->getTutorialProgress()['done']);
    }

    public function testDoneFlagFlipsToTrueWhenAllBranchStepsResolved(): void
    {
        $helper = app(TutorialHelper::class);
        $branchSteps = array_keys($helper->getStepsForBranch(TutorialHelper::BRANCH_EXPLORER));

        $progress = $this->user->getTutorialProgress();
        $progress['branch'] = TutorialHelper::BRANCH_EXPLORER;
        // Mark everything complete; group collapsing inside isTutorialComplete handles dupes.
        $progress['completed'] = $branchSteps;
        $this->user->setTutorialProgress($progress);
        $this->user->save();

        $this->service->evaluateAndSync($this->user, $this->dominion);

        $this->assertTrue($this->user->getTutorialProgress()['done']);
    }
}
