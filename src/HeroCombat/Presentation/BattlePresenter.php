<?php

namespace OpenDominion\HeroCombat\Presentation;

use OpenDominion\HeroCombat\Engine\CombatantState;
use OpenDominion\HeroCombat\Persistence\BattleRepository;
use OpenDominion\Models\HeroBattle;

class BattlePresenter
{
    public function __construct(protected BattleRepository $repository)
    {
    }

    /**
     * A view of the battle for the dominion, with the combatants it pilots.
     */
    public function present(HeroBattle $heroBattle, ?int $viewerDominionId = null): BattleView
    {
        $battle = $this->repository->load($heroBattle);

        $controlled = array_values(array_filter(
            $battle->combatants(),
            fn (CombatantState $combatant) => $viewerDominionId !== null && $combatant->controllerDominionId === $viewerDominionId,
        ));

        return new BattleView($heroBattle, $battle, $controlled, $viewerDominionId);
    }
}
