<?php

namespace OpenDominion\Helpers;

use LogicException;
use OpenDominion\Models\Race;
use OpenDominion\Models\RacePerkType;

class RaceHelper
{
    public function getPerkDescriptionHtmlWithValue(RacePerkType $perkType): ?array
    {
        $valueType = '%';
        $booleanValue = false;
        switch($perkType->key) {
            case 'archmage_cost':
                $negativeBenefit = true;
                $description = 'Archmage cost';
                $valueType = 'p';
                break;
            case 'construction_cost':
                $negativeBenefit = true;
                $description = 'Construction cost';
                break;
            case 'defense':
                $negativeBenefit = false;
                $description = 'Defensive power';
                break;
            case 'extra_barren_max_population':
                $negativeBenefit = false;
                $description = 'Population from barren land';
                $valueType = '';
                break;
            case 'food_consumption':
                $negativeBenefit = true;
                $description = 'Food consumption';
                break;
            case 'food_production':
                $negativeBenefit = false;
                $description = 'Food production';
                break;
            case 'gem_production':
                $negativeBenefit = false;
                $description = 'Gem production';
                break;
            case 'immortal_wizards':
                $negativeBenefit = false;
                $description = 'Immortal wizards';
                $booleanValue = true;
                break;
            case 'immortal_spies':
                $negativeBenefit = false;
                $description = 'Immortal spies';
                $booleanValue = true;
                break;
            case 'invest_bonus':
                $negativeBenefit = false;
                $description = 'Castle bonuses';
                break;
            case 'lumber_production':
                $negativeBenefit = false;
                $description = 'Lumber production';
                break;
            case 'mana_production':
                $negativeBenefit = false;
                $description = 'Mana production';
                break;
            case 'max_population':
                $negativeBenefit = false;
                $description = 'Max population';
                break;
            case 'offense':
                $negativeBenefit = false;
                $description = 'Offensive power';
                break;
            case 'ore_production':
                $negativeBenefit = false;
                $description = 'Ore production';
                break;
            case 'platinum_production':
                $negativeBenefit = false;
                $description = 'Platinum production';
                break;
            case 'spy_strength':
                $negativeBenefit = false;
                $description = 'Spy strength';
                break;
            case 'wizard_strength':
                $negativeBenefit = false;
                $description = 'Wizard strength';
                break;
            case 'cannot_construct':
                $negativeBenefit = true;
                $description = 'Cannot construct buildings';
                $booleanValue = true;
                break;
            case 'boat_capacity':
                $negativeBenefit = false;
                $description = 'Increased boat capacity';
                $valueType = ' units/boat';
                break;
            case 'platinum_production':
                $negativeBenefit = false;
                $description = 'Platinum production';
                $booleanValue = false;
                break;
            case 'can_invest_mana':
                $negativeBenefit = false;
                $description = 'Can invest mana in castle';
                $booleanValue = true;
                break;
            case 'population_growth':
                $negativeBenefit = false;
                $description = 'Population growth rate';
                break;
          case 'cannot_improve_castle':
                $negativeBenefit = true;
                $description = 'Cannot use castle improvements';
                $booleanValue = true;
                break;
          case 'cannot_explore':
                $negativeBenefit = true;
                $description = 'Cannot explore';
                $booleanValue = true;
                break;
          case 'cannot_invade':
                $negativeBenefit = true;
                $description = 'Cannot invade';
                $booleanValue = true;
                break;
          case 'cannot_train_spies':
                $negativeBenefit = true;
                $description = 'Cannot train spies';
                $booleanValue = true;
                break;
          case 'cannot_train_wizards':
                $negativeBenefit = true;
                $description = 'Cannot train wizards';
                $booleanValue = true;
                break;
          case 'cannot_train_archmages':
                $negativeBenefit = true;
                $description = 'Cannot train Arch Mages';
                $booleanValue = true;
                break;
          case 'explore_cost':
                $negativeBenefit = true;
                $description = 'Cost of exploration';
                break;
            case 'reduce_conversions':
                $negativeBenefit = false;
                $description = 'Reduced conversions';
                break;
            case 'exchange_bonus':
                $negativeBenefit = false;
                $description = 'Better exchange rates';
                break;
            case 'guard_tax_exemption':
                $negativeBenefit = false;
                $description = 'Exempt from guard platinum tax';
                $booleanValue = true;
                break;
          case 'tissue_improvement':
                $negativeBenefit = false;
                $description = 'Tissue improvements';
                $booleanValue = true;
                break;
          case 'does_not_kill':
                $negativeBenefit = false;
                $description = 'Does not kill units.';
                $booleanValue = true;
                break;
          case 'gryphon_nests_generates_wild_yetis':
                $negativeBenefit = false;
                $description = 'Traps wild yetis';
                $booleanValue = true;
                break;
            case 'prestige_gains':
                $negativeBenefit = false;
                $description = 'Prestige gains';
                break;
            case 'draftee_dp':
                $negativeBenefit = true;
                $description = 'DP per draftee';
                $booleanValue = 'static';
                break;
            case 'increased_construction_speed':
                $negativeBenefit = false;
                $description = 'Increased construction speed';
                $valueType = ' hours';
                break;
            case 'all_units_trained_in_9hrs':
                $negativeBenefit = false;
                $description = 'All units trained in 9 ticks';
                $booleanValue = true;
                break;
            case 'extra_barracks_housing':
                $negativeBenefit = false;
                $description = 'Barracks housing';
                $valueType = ' units';
                break;
            case 'cannot_build_homes':
                $negativeBenefit = true;
                $description = 'Cannot build homes';
                $booleanValue = true;
                break;
            case 'castle_max':
                $negativeBenefit = false;
                $description = 'Castle improvements max';
                break;
            case 'tech_costs':
                $negativeBenefit = true;
                $description = 'Cost of technological advancements';
                break;
            case 'experience_points_per_acre':
                $negativeBenefit = false;
                $description = 'Experience points gained per acre on successful invasions';
                break;
            case 'cannot_tech':
                $negativeBenefit = true;
                $description = 'Cannot use technological advancements';
                $booleanValue = true;
                break;
            case 'peasants_produce_food':
                $negativeBenefit = true;
                $description = 'Food/tick per peasant';
                $booleanValue = 'static';
                break;
            case 'no_lumber_construction_cost':
                $negativeBenefit = false;
                $description = 'No lumber construction cost';
                $booleanValue = true;
                break;
            case 'ore_improvement_points':
                $negativeBenefit = false;
                $description = 'Improvement points from ore';
                break;
            case 'research_points_per_acre':
                $negativeBenefit = false;
                $description = 'Experience points per acre on invasions';
                break;
            case 'immune_to_lightning_bolt':
                $negativeBenefit = false;
                $description = 'No damage from lightning bolts';
                $booleanValue = true;
                break;
            case 'construction_material':
                $negativeBenefit = false;
                $description = 'Buildings only cost';
                $booleanValue = 'static';
                break;
            default:
                return null;
        }

        $result = ['description' => $description, 'value' => ''];
        $valueString = "{$perkType->pivot->value}{$valueType}";

        if ($perkType->pivot->value < 0)
        {

            if($booleanValue === true)
            {
                $valueString = 'No';
            }

            if($booleanValue == 'static')
            {
              $valueString = $perkType->pivot->value;;
            }

            if ($negativeBenefit === true)
            {
                $result['value'] = "<span class=\"text-green\">{$valueString}</span>";
            }
            elseif($booleanValue == 'static')
            {
                $result['value'] = "<span class=\"text-blue\">{$valueString}</span>";
            }
            else
            {
                $result['value'] = "<span class=\"text-red\">{$valueString}</span>";
            }
        }
        else
        {
            $prefix = '+';
            if($booleanValue === true)
            {
                $valueString = 'Yes';
                $prefix = '';
            }
            elseif($booleanValue == 'static')
            {
              $valueString = $perkType->pivot->value;
              $prefix = '';
            }

            if ($negativeBenefit === true)
            {
                $result['value'] = "<span class=\"text-red\">{$prefix}{$valueString}</span>";
            }
            elseif($booleanValue == 'static')
            {
                $result['value'] = "<span class=\"text-blue\">{$prefix}{$valueString}</span>";
            }
            else
            {
                $result['value'] = "<span class=\"text-green\">{$prefix}{$valueString}</span>";
            }
        }

        return $result;
    }


    /*
        public function getPerkDescriptionHtml(RacePerkType $perkType): string
        {
            switch($perkType->key) {
                case 'archmage_cost':
                    $negativeBenefit = true;
                    $description = 'archmage cost';
                    break;
                case 'construction_cost':
                    $negativeBenefit = true;
                    $description = 'construction cost';
                    break;
                case 'defense':
                    $negativeBenefit = false;
                    $description = 'defensive power';
                    break;
                case 'extra_barren_max_population':
                    $negativeBenefit = false;
                    $description = 'population from barren land';
                    break;
                case 'food_consumption':
                    $negativeBenefit = true;
                    $description = 'food consumption';
                    break;
                case 'food_production':
                    $negativeBenefit = false;
                    $description = 'food production';
                    break;
                case 'gem_production':
                    $negativeBenefit = false;
                    $description = ' gem production';
                    break;
                case 'immortal_wizards':
                    $negativeBenefit = false;
                    $description = 'immortal wizards';
                    break;
                case 'immortal_spies':
                    $negativeBenefit = false;
                    $description = 'immortal spies';
                    break;
                case 'invest_bonus':
                    $negativeBenefit = false;
                    $description = 'castle bonuses';
                    break;
                case 'lumber_production':
                    $negativeBenefit = false;
                    $description = 'lumber production';
                    break;
                case 'mana_production':
                    $negativeBenefit = false;
                    $description = 'mana production';
                    break;
                case 'max_population':
                    $negativeBenefit = false;
                    $description = 'max population';
                    break;
                case 'offense':
                    $negativeBenefit = false;
                    $description = 'offensive power';
                    break;
                case 'ore_production':
                    $negativeBenefit = false;
                    $description = 'ore production';
                    break;
                case 'platinum_production':
                    $negativeBenefit = false;
                    $description = 'platinum production';
                    break;
                case 'spy_strength':
                    $negativeBenefit = false;
                    $description = 'spy strength';
                    break;
                case 'wizard_strength':
                    $negativeBenefit = false;
                    $description = 'wizard strength';
                    break;
                case 'cannot_construct':
                    $negativeBenefit = false;
                    $description = 'difficulty: cannot construct buildings';
                    break;
                case 'boat_capacity':
                    $negativeBenefit = false;
                    $description = 'boat capacity';
                    break;
                case 'platinum_production':
                    $negativeBenefit = false;
                    $description = 'platinum production';
                    break;
                case 'can_invest_mana':
                    $negativeBenefit = false;
                    $description = 'can invest mana in castle';
                    break;
                case 'population_growth':
                    $negativeBenefit = false;
                    $description = 'population growth rate';
                    break;
                case 'cannot_improve_castle':
                    $negativeBenefit = false;
                    $description = 'cannot use castle improvements';
                    break;
                case 'cannot_explore':
                    $negativeBenefit = false;
                    $description = 'cannot explore';
                    break;
                case 'cannot_invade':
                    $negativeBenefit = false;
                    $description = 'cannot explore';
                    break;
                case 'cannot_train_spies':
                    $negativeBenefit = false;
                    $description = 'cannot train spies';
                    break;
                case 'cannot_train_wizards':
                    $negativeBenefit = false;
                    $description = 'cannot train wizards';
                    break;
                case 'cannot_train_archmages':
                    $negativeBenefit = false;
                    $description = 'cannot train Arch Mages';
                    break;
                case 'explore_cost':
                    $negativeBenefit = false;
                    $description = 'cost of exploration';
                    break;
                case 'reduce_conversions':
                    $negativeBenefit = false;
                    $description = 'reduced conversions';
                    break;
                case 'exchange_bonus':
                    $negativeBenefit = false;
                    $description = 'better exchange rates';
                    break;
                case 'guard_tax_exemption':
                    $negativeBenefit = false;
                    $description = 'No guard platinum tax';
                    break;
                case 'tissue_improvement':
                    $negativeBenefit = false;
                    $description = 'Can improve tissue (only)';
                    break;
                case 'does_not_kill':
                    $negativeBenefit = false;
                    $description = 'Does not kill enemy units';
                    break;
                case 'gryphon_nests_generates_wild_yetis':
                    $negativeBenefit = false;
                    $description = 'Traps wild yetis';
                    break;
                case 'prestige_gains':
                    $negativeBenefit = false;
                    $description = 'prestige gains';
                    break;
                case 'draftee_dp':
                    $negativeBenefit = true;
                    $description = 'DP per draftee';
                    break;
                case 'increased_construction_speed':
                    $negativeBenefit = false;
                    $description = 'increased construction speed';
                    break;
                case 'all_units_trained_in_9hrs':
                    $negativeBenefit = false;
                    $description = 'All units trained in 9 ticks';
                    break;
                case 'extra_barracks_housing':
                    $negativeBenefit = false;
                    $description = 'Barracks housing';
                    break;
                case 'cannot_build_homes':
                    $negativeBenefit = true;
                    $description = 'cannot build homes';
                    break;
                case 'castle_max':
                    $negativeBenefit = false;
                    $description = 'castle improvements max';
                    break;
                case 'tech_costs':
                    $negativeBenefit = true;
                    $description = 'cost of technological advancements';
                    break;
                case 'experience_points_per_acre':
                    $negativeBenefit = false;
                    $description = 'experience points gained per acre on successful invasions';
                    break;
                case 'cannot_tech':
                    $negativeBenefit = true;
                    $description = 'cannot use technological advancements';
                    $booleanValue = true;
                    break;
                case 'peasants_produce_food':
                    $negativeBenefit = true;
                    $description = 'food/tick per peasant';
                    $booleanValue = 'static';
                    break;
                case 'no_lumber_construction_cost':
                    $negativeBenefit = false;
                    $description = 'no lumber construction cost';
                    $booleanValue = true;
                    break;
                case 'ore_improvement_points':
                    $negativeBenefit = false;
                    $description = 'improvement points from ore';
                    break;
                case 'research_points_per_acre':
                    $negativeBenefit = false;
                    $description = 'experience points per acre on invasions';
                    break;
                case 'immune_to_lightning_bolt':
                    $negativeBenefit = false;
                    $description = 'no damage from lightning bolts';
                    $booleanValue = true;
                    break;
                default:
                    return '';
            }

            if ($perkType->pivot->value < 0) {
                if ($negativeBenefit) {
                    return "<span class=\"text-green\">Decreased {$description}</span>";
                } else {
                    return "<span class=\"text-red\">Decreased {$description}</span>";
                }
            } else {
                if ($negativeBenefit) {
                    return "<span class=\"text-red\">Increased {$description}</span>";
                } else {
                    return "<span class=\"text-green\">Increased {$description}</span>";
                }
            }
        }
    */

}
