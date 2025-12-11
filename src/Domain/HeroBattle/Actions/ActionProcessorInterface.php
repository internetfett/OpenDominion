<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Context\CombatContext;

interface ActionProcessorInterface
{
    /**
     * Process the action and modify the combat context
     *
     * @param CombatContext $context
     * @return void
     */
    public function process(CombatContext $context): void;

    /**
     * Get the action key this processor handles
     *
     * @return string
     */
    public function getActionKey(): string;
}
