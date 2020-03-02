<?php

namespace OpenDominion\Helpers;

use LogicException;

class HistoryHelper
{
    public function getAction(string $action): array
    {

        $return = [];

        switch($action)
        {
            case 'tick':
                $icon = 'ra ra-hourglass ra-fw';
                $text = 'Tick';
                break;
            case 'invade':
                $icon = 'ra ra-crossed-swords ra-fw';
                $text = 'Invasion';
                break;
            case 'construct':
                $icon = 'fa fa-home fa-fw';
                $text = 'Invasion';
                break;
            case 'rezone':
                $icon = 'fa fa-refresh fa-fw';
                $text = 'Rezoning';
                break;
            case 'change draft rate':
                $icon = 'ra ra-sword ra-fw';
                $text = 'Draft rate';
                break;
            case 'daily bonus':
                $icon = 'fa fa-plus fa-fw';
                $text = 'Daily bonus';
                break;
            case 'tech':
                $icon = 'fa fa-flask fa-fw';
                $text = 'Advancement';
                break;
            case 'improve':
                $icon = 'fa fa-arrow-up fa-fw';
                $text = 'Improvement';
                break;
            case 'train':
                $icon = 'ra ra-sword ra-fw';
                $text = 'Training';
                break;
            case 'cast spell':
                $icon = 'ra ra-fairy-wand ra-fw';
                $text = 'Spell';
                break;
            case 'release':
                $icon = 'ra ra-sword ra-fw';
                $text = 'Release';
                break;
            case 'perform espionage operation':
                $icon = 'fa fa-user-secret fa-fw';
                $text = 'Espionage';
                break;
            case 'bank':
                $icon = 'fa fa-money fa-fw';
                $text = 'Exchange';
                break;
            default:
                $icon = 'ra ra-help ra-fw';
                $text = 'Undefined';
                break;
        }

        $return['icon'] = $icon;
        $return['text'] = $text;

      return $action;
    }

}
