<?php

namespace OpenDominion\Services\User;

use OpenDominion\Helpers\TutorialHelper;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\User;
use OpenDominion\Services\Dominion\HistoryService;
use OpenDominion\Services\Dominion\ProtectionService;

class TutorialService
{
    public function __construct(
        protected TutorialHelper $tutorialHelper,
        protected ProtectionService $protectionService,
    ) {}

    /**
     * Evaluate auto-completion predicates for in-phase, applicable, incomplete steps;
     * persist any newly-completed/skipped steps; recompute the `done` flag.
     *
     * Returns the same shape as getState().
     */
    public function evaluateAndSync(User $user, Dominion $dominion): array
    {
        $progress = $user->getTutorialProgress();
        $phase = $this->currentPhase($dominion);
        $branch = $progress['branch'] ?? null;

        $ctx = $this->buildContext($user, $dominion, $progress);
        $changed = false;

        $candidates = $this->candidateSteps($phase, $branch);

        foreach ($candidates as $id => $step) {
            if (in_array($id, $progress['completed'], true) || in_array($id, $progress['skipped'], true)) {
                continue;
            }

            if (!($step['applies_to'])($dominion, $user, $ctx)) {
                $progress['skipped'][] = $id;
                $changed = true;
                continue;
            }

            if (($step['completed_when'])($dominion, $user, $ctx)) {
                $progress['completed'][] = $id;
                $changed = true;
            }
        }

        $done = $this->isTutorialComplete($progress);
        if ($done !== ($progress['done'] ?? false)) {
            $progress['done'] = $done;
            $changed = true;
        }

        if ($changed) {
            $user->setTutorialProgress($progress);
            $user->save();
        }

        return $this->buildState($user, $dominion, $progress);
    }

    /**
     * Read-only view of the tutorial state. Does not write.
     */
    public function getState(User $user, ?Dominion $dominion): array
    {
        $progress = $user->getTutorialProgress();
        return $this->buildState($user, $dominion, $progress);
    }

    /**
     * Mark a step complete. Only honored for steps flagged `manual_complete`.
     */
    public function markComplete(User $user, string $stepId): bool
    {
        $step = $this->tutorialHelper->getStep($stepId);
        if ($step === null || empty($step['manual_complete'])) {
            return false;
        }

        $progress = $user->getTutorialProgress();
        if (in_array($stepId, $progress['completed'], true)) {
            return true;
        }

        $progress['completed'][] = $stepId;
        $progress['done'] = $this->isTutorialComplete($progress);
        $user->setTutorialProgress($progress);
        $user->save();
        return true;
    }

    /**
     * Skip a step. Only honored for steps flagged `skippable`.
     */
    public function skip(User $user, string $stepId): bool
    {
        $step = $this->tutorialHelper->getStep($stepId);
        if ($step === null || empty($step['skippable'])) {
            return false;
        }

        $progress = $user->getTutorialProgress();
        if (in_array($stepId, $progress['skipped'], true) || in_array($stepId, $progress['completed'], true)) {
            return true;
        }

        $progress['skipped'][] = $stepId;
        $progress['done'] = $this->isTutorialComplete($progress);
        $user->setTutorialProgress($progress);
        $user->save();
        return true;
    }

    /**
     * Choose attacker/explorer branch. Also marks the `choose_branch` step complete.
     */
    public function chooseBranch(User $user, string $branch): bool
    {
        if (!in_array($branch, $this->tutorialHelper->getBranches(), true)) {
            return false;
        }

        $progress = $user->getTutorialProgress();
        $progress['branch'] = $branch;

        if (!in_array('choose_branch', $progress['completed'], true)) {
            $progress['completed'][] = 'choose_branch';
        }

        $progress['done'] = $this->isTutorialComplete($progress);
        $user->setTutorialProgress($progress);
        $user->save();
        return true;
    }

    // ---------------- internals ----------------

    /**
     * Which phase the dominion is in. Returns null when no dominion is available.
     */
    protected function currentPhase(?Dominion $dominion): ?string
    {
        if ($dominion === null) {
            return null;
        }

        if (!$dominion->round->hasStarted()) {
            return TutorialHelper::PHASE_PRE_ROUND;
        }

        if ($this->protectionService->isUnderProtection($dominion)) {
            return TutorialHelper::PHASE_PROTECTION;
        }

        return TutorialHelper::PHASE_ACTIVE;
    }

    /**
     * Steps eligible for the current phase + branch, sorted.
     * Active phase includes pre-active phases' steps as well (so manual-complete
     * pre_round steps don't get stranded once the round starts).
     */
    protected function candidateSteps(?string $phase, ?string $branch): array
    {
        if ($phase === null) {
            return [];
        }

        $allowedPhases = match ($phase) {
            TutorialHelper::PHASE_PRE_ROUND => [TutorialHelper::PHASE_PRE_ROUND],
            TutorialHelper::PHASE_PROTECTION => [TutorialHelper::PHASE_PRE_ROUND, TutorialHelper::PHASE_PROTECTION],
            TutorialHelper::PHASE_ACTIVE => [TutorialHelper::PHASE_PRE_ROUND, TutorialHelper::PHASE_PROTECTION, TutorialHelper::PHASE_ACTIVE],
            default => [],
        };

        $steps = array_filter(
            $this->tutorialHelper->getStepsForBranch($branch),
            fn($s) => in_array($s['phase'], $allowedPhases, true),
        );

        uasort($steps, fn($a, $b) => $a['order'] <=> $b['order']);
        return $steps;
    }

    /**
     * Pre-compute shared lookups passed to step predicates.
     * Pulled once per evaluation pass to avoid N+1.
     */
    protected function buildContext(User $user, ?Dominion $dominion, array $progress): array
    {
        $historyEvents = [];
        if ($dominion !== null) {
            $historyEvents = $dominion->history()
                ->whereIn('event', [
                    HistoryService::EVENT_ACTION_CAST_SPELL,
                    HistoryService::EVENT_ACTION_EXPLORE,
                    HistoryService::EVENT_ACTION_INVADE,
                    HistoryService::EVENT_ACTION_PERFORM_ESPIONAGE_OPERATION,
                ])
                ->pluck('event')
                ->unique()
                ->values()
                ->all();
        }

        return [
            'branch' => $progress['branch'] ?? null,
            'history_events' => $historyEvents,
        ];
    }

    /**
     * Assemble the state array consumed by the widget. Applies group collapsing,
     * applicability filtering, and progress counts.
     */
    protected function buildState(User $user, ?Dominion $dominion, array $progress): array
    {
        $phase = $this->currentPhase($dominion);
        $branch = $progress['branch'] ?? null;
        $candidates = $this->candidateSteps($phase, $branch);

        $completedIds = $progress['completed'];
        $skippedIds = $progress['skipped'];

        // Resolve a context for any UI-side applicability checks. Cheap if no dominion.
        $ctx = $this->buildContext($user, $dominion, $progress);

        $current = null;
        $upcoming = [];
        $completed = [];
        $skipped = [];
        $notApplicable = [];

        // Track which groups already have a surfaced step this pass.
        $groupsClaimed = [];
        // A group is "satisfied" when any member is in completed → other members hide.
        $satisfiedGroups = [];
        foreach ($candidates as $id => $step) {
            if ($step['group'] !== null && in_array($id, $completedIds, true)) {
                $satisfiedGroups[$step['group']] = true;
            }
        }

        foreach ($candidates as $id => $step) {
            $stepWithId = $step + ['id' => $id];

            if (in_array($id, $completedIds, true)) {
                $completed[$id] = $stepWithId;
                continue;
            }

            if (in_array($id, $skippedIds, true)) {
                $skipped[$id] = $stepWithId;
                continue;
            }

            // Group already satisfied by a completed sibling — hide this one.
            if ($step['group'] !== null && !empty($satisfiedGroups[$step['group']])) {
                continue;
            }

            // Race / role applicability — defensive UI check; auto-skip happens in evaluateAndSync.
            if ($dominion !== null && !($step['applies_to'])($dominion, $user, $ctx)) {
                $notApplicable[$id] = $stepWithId;
                continue;
            }

            // Group with no completion yet: only the first not-yet-claimed member surfaces.
            if ($step['group'] !== null) {
                if (!empty($groupsClaimed[$step['group']])) {
                    continue;
                }
                $groupsClaimed[$step['group']] = true;
            }

            if ($current === null) {
                $current = $stepWithId;
            } else {
                $upcoming[$id] = $stepWithId;
            }
        }

        $totalForProgress = count($candidates) - count($notApplicable);
        $doneCount = count($completed) + count($skipped);

        return [
            'current' => $current,
            'upcoming' => $upcoming,
            'completed' => $completed,
            'skipped' => $skipped,
            'not_applicable' => $notApplicable,
            'progress' => [
                'completed' => $doneCount,
                'total' => $totalForProgress,
            ],
            'branch' => $branch,
            'phase' => $phase,
            'done' => $progress['done'] ?? false,
        ];
    }

    /**
     * Tutorial is complete when no step is left to surface across all phases for
     * the chosen branch. A user with no branch chosen can never be "done".
     */
    protected function isTutorialComplete(array $progress): bool
    {
        if (empty($progress['branch'])) {
            return false;
        }

        // Look at the full set across all phases — this is independent of where the
        // user currently is, so the flag stays sticky once flipped.
        $all = $this->tutorialHelper->getStepsForBranch($progress['branch']);
        uasort($all, fn($a, $b) => $a['order'] <=> $b['order']);

        $satisfiedGroups = [];
        foreach ($all as $id => $step) {
            if ($step['group'] !== null && in_array($id, $progress['completed'], true)) {
                $satisfiedGroups[$step['group']] = true;
            }
        }

        foreach ($all as $id => $step) {
            if (in_array($id, $progress['completed'], true) || in_array($id, $progress['skipped'], true)) {
                continue;
            }
            if ($step['group'] !== null && !empty($satisfiedGroups[$step['group']])) {
                continue;
            }
            // Applicability isn't checked here — `evaluateAndSync` records inapplicable
            // steps as skipped before this method runs, so leftover steps here are
            // genuinely outstanding.
            return false;
        }

        return true;
    }
}
