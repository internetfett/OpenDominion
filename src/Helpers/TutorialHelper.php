<?php

namespace OpenDominion\Helpers;

use OpenDominion\Models\Council;
use OpenDominion\Models\Dominion;
use OpenDominion\Models\User;
use OpenDominion\Services\Dominion\HistoryService;

class TutorialHelper
{
    public const PHASE_PRE_ROUND = 'pre_round';
    public const PHASE_PROTECTION = 'protection';
    public const PHASE_ACTIVE = 'active';

    public const BRANCH_EXPLORER = 'explorer';
    public const BRANCH_ATTACKER = 'attacker';

    public const GROUP_COMMUNITY_INTRO = 'community_intro';

    /**
     * Returns all tutorial step definitions, keyed by step id.
     *
     * Step shape:
     *   title:          short headline shown to the player
     *   description:    one-or-two-sentence explanation of why / how
     *   phase:          PHASE_* — when the step is eligible to appear
     *   branch:         null | BRANCH_* — null means applies to both branches
     *   group:          string|null — only one applicable, non-skipped step per group renders
     *   order:          int sort key within a phase/branch
     *   skippable:      bool — show a "Skip" button
     *   manual_complete:bool — completion comes from a controller POST (only valid when true)
     *   action_route:   string|null — named route the action button links to
     *   applies_to:     fn(Dominion, User, array $ctx): bool — race / role applicability
     *   completed_when: fn(Dominion, User, array $ctx): bool — auto-completion predicate
     *   not_applicable_reason: string|null — shown when applies_to is false
     */
    public function getSteps(): array
    {
        return [

            // ---------- pre-round ----------

            'welcome' => [
                'title'           => 'Welcome to OpenDominion',
                'description'     => "You're playing a long-form strategy game. Each tick is one hour of game time. This helper will guide you through your first round — follow the steps and you'll learn the ropes without getting stomped.",
                'phase'           => self::PHASE_PRE_ROUND,
                'branch'          => null,
                'group'           => null,
                'order'           => 10,
                'skippable'       => false,
                'manual_complete' => true,
                'action_route'    => null,
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) => false,
            ],

            'visit_scribes' => [
                'title'           => 'Visit the Scribes',
                'description'     => "The Scribes are your in-game reference. Browse your race's unit roster, perks, and spells — it's worth knowing what you can actually do before the round starts.",
                'phase'           => self::PHASE_PRE_ROUND,
                'branch'          => null,
                'group'           => null,
                'order'           => 20,
                'skippable'       => false,
                'manual_complete' => true,
                'action_route'    => 'scribes.overview',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) => false,
            ],

            // ---------- protection ----------

            'protection_build_advisor' => [
                'title'           => 'Plan your protection build',
                'description'     => "Use the building advisor to plan a balanced opening. New players usually start with homes, farms, and resource buildings before training military.",
                'phase'           => self::PHASE_PROTECTION,
                'branch'          => null,
                'group'           => null,
                'order'           => 10,
                'skippable'       => false,
                'manual_complete' => true,
                'action_route'    => 'dominion.advisors.production',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) => false,
            ],

            'protection_build_homes' => [
                'title'           => 'Build homes for your population',
                'description'     => "Homes raise your maximum population, which feeds every other system: jobs, military, taxes. Construct at least 5 homes during protection.",
                'phase'           => self::PHASE_PROTECTION,
                'branch'          => null,
                'group'           => null,
                'order'           => 20,
                'skippable'       => false,
                'manual_complete' => false,
                'action_route'    => 'dominion.construct',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) => $d->building_home >= 5,
            ],

            'protection_cast_spell' => [
                'title'           => 'Cast a self-buff spell',
                'description'     => "Self-buffs like Gaia's Watch and Midas Touch boost food and platinum production. Get into the habit of casting them every cycle.",
                'phase'           => self::PHASE_PROTECTION,
                'branch'          => null,
                'group'           => null,
                'order'           => 30,
                'skippable'       => false,
                'manual_complete' => false,
                'action_route'    => 'dominion.magic',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) =>
                    in_array(HistoryService::EVENT_ACTION_CAST_SPELL, $ctx['history_events'] ?? [], true),
            ],

            // ---------- choose your branch ----------

            'choose_branch' => [
                'title'           => 'Choose your path: Attacker or Explorer',
                'description'     => "Veterans usually recommend exploring for your first round — it's safer and teaches the economy without the risk of losing your army. Pick Attacker if you want a more aggressive learning path.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => null,
                'group'           => null,
                'order'           => 10,
                'skippable'       => false,
                'manual_complete' => false,
                'action_route'    => null,
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                // Completed when the user has picked a branch (handled in TutorialService).
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) =>
                    !empty($ctx['branch']),
            ],

            // ---------- active — explorer branch ----------

            'explorer_train_draftees' => [
                'title'           => 'Build up your draftees',
                'description'     => "Draftees are required to explore new land. Use the Military page to raise your draft rate and train at least 200 draftees.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => self::BRANCH_EXPLORER,
                'group'           => null,
                'order'           => 20,
                'skippable'       => false,
                'manual_complete' => false,
                'action_route'    => 'dominion.military',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) => $d->military_draftees >= 200,
            ],

            'explorer_explore_land' => [
                'title'           => 'Send your first exploration',
                'description'     => "Exploring is how you grow without invading. It costs platinum, food, and draftees per acre — but it's safe.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => self::BRANCH_EXPLORER,
                'group'           => null,
                'order'           => 30,
                'skippable'       => false,
                'manual_complete' => false,
                'action_route'    => 'dominion.explore',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) =>
                    in_array(HistoryService::EVENT_ACTION_EXPLORE, $ctx['history_events'] ?? [], true),
            ],

            'explorer_research_tech' => [
                'title'           => 'Start your first tech research',
                'description'     => "Tech points unlock permanent bonuses. Pick a starter tech that fits your strategy — production techs are a safe bet for explorers.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => self::BRANCH_EXPLORER,
                'group'           => null,
                'order'           => 40,
                'skippable'       => false,
                'manual_complete' => false,
                'action_route'    => 'dominion.techs',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) => $d->techs()->exists(),
            ],

            // ---------- active — attacker branch ----------

            'attacker_train_offense' => [
                'title'           => 'Train offensive units',
                'description'     => "Attackers need offensive military units (slots 3 and 4 for most races). Train at least 100 to be a credible threat.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => self::BRANCH_ATTACKER,
                'group'           => null,
                'order'           => 20,
                'skippable'       => false,
                'manual_complete' => false,
                'action_route'    => 'dominion.military',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) =>
                    ($d->military_unit3 + $d->military_unit4) >= 100,
            ],

            'attacker_first_invasion' => [
                'title'           => 'Launch your first invasion',
                'description'     => "Pick a target with weak defense — check the Op Center first. Your army returns over 12 ticks, so plan your defenses while they're gone.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => self::BRANCH_ATTACKER,
                'group'           => null,
                'order'           => 30,
                'skippable'       => false,
                'manual_complete' => false,
                'action_route'    => 'dominion.invade',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) =>
                    in_array(HistoryService::EVENT_ACTION_INVADE, $ctx['history_events'] ?? [], true),
            ],

            // ---------- active — both branches ----------

            'build_dock' => [
                'title'           => 'Build a Dock',
                'description'     => "Your race has units that need boats to invade. Docks let you ferry them — without enough, your offense is grounded.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => null,
                'group'           => null,
                'order'           => 50,
                'skippable'       => false,
                'manual_complete' => false,
                'action_route'    => 'dominion.construct',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) =>
                    $d->race->units
                        ->where('power_offense', '>', 0)
                        ->where('need_boat', true)
                        ->isNotEmpty(),
                'not_applicable_reason' => "Your race's offensive units don't use boats — you don't need docks.",
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) =>
                    $d->building_dock > 0
                    || $d->queues()->where('resource', 'building_dock')->exists(),
            ],

            // ---------- community intro group: Discord OR Council ----------

            'join_discord' => [
                'title'           => 'Join the Discord',
                'description'     => "The OpenDominion Discord is where most strategy talk happens. Linking your account also unlocks Discord-only realm channels.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => null,
                'group'           => self::GROUP_COMMUNITY_INTRO,
                'order'           => 60,
                'skippable'       => true,
                'manual_complete' => false,
                'action_route'    => 'settings',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) => $u->discordUser !== null,
            ],

            'post_in_council' => [
                'title'           => 'Post in your realm Council',
                'description'     => "If Discord isn't for you, introduce yourself in the Council. Your realmies are your single biggest source of advice this round.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => null,
                'group'           => self::GROUP_COMMUNITY_INTRO,
                'order'           => 61,
                'skippable'       => true,
                'manual_complete' => false,
                'action_route'    => 'dominion.council',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) =>
                    Council\Post::where('dominion_id', $d->id)->exists(),
            ],

            // ---------- graduation ----------

            'graduation' => [
                'title'           => 'Tutorial complete — ask your realm what to do next',
                'description'     => "You've built a stable dominion. The real game from here is social: coordinate invasions, fund wonders, and learn from your realmies. Good luck.",
                'phase'           => self::PHASE_ACTIVE,
                'branch'          => null,
                'group'           => null,
                'order'           => 100,
                'skippable'       => false,
                'manual_complete' => true,
                'action_route'    => 'dominion.council',
                'applies_to'      => fn(Dominion $d, User $u, array $ctx = []) => true,
                'completed_when'  => fn(Dominion $d, User $u, array $ctx = []) => false,
            ],

        ];
    }

    public function getStep(string $id): ?array
    {
        return $this->getSteps()[$id] ?? null;
    }

    public function getStepsForPhase(string $phase): array
    {
        return array_filter($this->getSteps(), fn($s) => $s['phase'] === $phase);
    }

    /**
     * Steps for the given branch — includes steps with no branch (apply to both).
     */
    public function getStepsForBranch(?string $branch): array
    {
        return array_filter(
            $this->getSteps(),
            fn($s) => $s['branch'] === null || $s['branch'] === $branch
        );
    }

    /**
     * Valid branch values.
     */
    public function getBranches(): array
    {
        return [self::BRANCH_EXPLORER, self::BRANCH_ATTACKER];
    }
}
