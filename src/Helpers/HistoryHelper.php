<?php

namespace OpenDominion\Helpers;

use LogicException;

class HistoryHelper
{

    public function getEventIcon(string $event): string
    {
        $icons = [
            'tick' => 'ra ra-hourglass ra-fw',
            'invade' => 'ra ra-crossed-swords ra-fw',
            'construct' => 'fa fa-home fa-fw',
            'rezone' => 'fa fa-refresh fa-fw',
            'change draft rate' => 'ra ra-sword ra-fw',
            'daily bonus' => 'fa fa-plus fa-fw',
            'tech' => 'fa fa-flask fa-fw',
            'improve' => 'fa fa-arrow-up fa-fw',
            'train' => 'ra ra-sword ra-fw',
            'cast spell' => 'ra ra-fairy-wand ra-fw',
            'release' => 'ra ra-sword ra-fw',
            'perform espionage operation' => 'fa fa-user-secret fa-fw',
            'bank' => 'fa fa-money fa-fw',
        ];

        return $icons[$event];
    }

    public function getEventName(string $event): string
    {
        $icons = [
            'tick' => 'Tick',
            'invade' => 'Invasion',
            'construct' => 'Construction',
            'rezone' => 'Rezoning',
            'change draft rate' => 'Draft rate',
            'daily bonus' => 'Daily bonus',
            'tech' => 'Advancement',
            'improve' => 'Improvement',
            'train' => 'Training',
            'cast spell' => 'Spell',
            'release' => 'Release',
            'perform espionage operation' => 'Espionage',
            'bank' => 'Exchange',
        ];

        return $icons[$event];
    }

}
