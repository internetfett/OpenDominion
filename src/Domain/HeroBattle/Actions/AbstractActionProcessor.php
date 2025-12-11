<?php

namespace OpenDominion\Domain\HeroBattle\Actions;

use OpenDominion\Calculators\Dominion\HeroCalculator;
use OpenDominion\Domain\HeroBattle\Context\CombatContext;

abstract class AbstractActionProcessor implements ActionProcessorInterface
{
    protected HeroCalculator $heroCalculator;
    protected string $actionKey;

    public function __construct(HeroCalculator $heroCalculator, string $actionKey)
    {
        $this->heroCalculator = $heroCalculator;
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
