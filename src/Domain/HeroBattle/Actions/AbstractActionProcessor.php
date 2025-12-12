<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Domain\HeroBattle\Combat\CombatCalculator;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;

abstract class AbstractActionProcessor implements ActionProcessorInterface
{
    protected CombatCalculator $combatCalculator;
    protected string $actionKey;

    public function __construct(CombatCalculator $combatCalculator, string $actionKey)
    {
        $this->combatCalculator = $combatCalculator;
        $this->actionKey = $actionKey;
    }

    public function getActionKey(): string
    {
        return $this->actionKey;
    }

    /**
     * Get formatted message from action definition
     */
    protected function getMessage(CombatContext $context, string $messageKey, ...$args): string
    {
        $template = $context->actionDef['messages'][$messageKey] ?? '';
        return sprintf($template, ...$args);
    }
}
