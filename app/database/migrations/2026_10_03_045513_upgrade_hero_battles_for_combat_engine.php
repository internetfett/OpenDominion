<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moves hero battles onto the team-based combat engine (src/HeroCombat):
 * teams, effect instances, cooldowns, charges, structured log events, the seed plus
 * initial state needed to replay a battle, combatant controllers (borrowed heroes) and the
 * daily PvP experience reward.
 */
class UpgradeHeroBattlesForCombatEngine extends Migration
{
    public function up(): void
    {
        Schema::table('hero_battles', function (Blueprint $table) {
            $table->string('mode')->default('pvp')->after('pvp');
            $table->string('encounter_key')->nullable()->after('mode');
            $table->unsignedInteger('seed')->default(0)->after('encounter_key');
            $table->unsignedTinyInteger('winning_team')->nullable()->after('winner_combatant_id');
            $table->text('effects')->nullable()->after('winning_team');
            $table->longText('initial_state')->nullable()->after('effects');
        });

        Schema::table('hero_combatants', function (Blueprint $table) {
            $table->unsignedInteger('controller_dominion_id')->nullable()->after('dominion_id');
            $table->unsignedTinyInteger('team')->default(1)->after('controller_dominion_id');
            $table->string('template_key')->nullable()->after('team');
            $table->text('effects')->nullable()->after('abilities');
            $table->text('cooldowns')->nullable()->after('effects');
            $table->text('charges')->nullable()->after('cooldowns');

            $table->foreign('controller_dominion_id')->references('id')->on('dominions');
        });

        Schema::table('heroes', function (Blueprint $table) {
            $table->timestamp('last_pvp_reward_at')->nullable()->after('experience');
        });

        Schema::table('hero_battle_actions', function (Blueprint $table) {
            $table->unsignedInteger('combatant_id')->nullable()->change();
            $table->text('description')->change();
            $table->text('events')->nullable()->after('description');
        });

        DB::table('hero_combatants')
            ->whereNotNull('hero_id')
            ->update(['controller_dominion_id' => DB::raw('dominion_id')]);

        DB::table('hero_battles')->whereNotNull('raid_tactic_id')->update(['mode' => 'raid']);
        DB::table('hero_battles')->whereNull('raid_tactic_id')->where('pvp', false)->update(['mode' => 'practice']);

        DB::table('hero_combatants')
            ->join('hero_battles', 'hero_battles.id', '=', 'hero_combatants.hero_battle_id')
            ->where('hero_battles.pvp', false)
            ->update(['hero_combatants.team' => DB::raw('IF(hero_combatants.hero_id IS NULL, 2, 1)')]);

        DB::table('hero_battles')->where('pvp', true)->orderBy('id')->chunkById(500, function ($battles) {
            foreach ($battles as $battle) {
                $combatantIds = DB::table('hero_combatants')
                    ->where('hero_battle_id', $battle->id)
                    ->orderBy('id')
                    ->pluck('id');
                foreach ($combatantIds as $index => $combatantId) {
                    DB::table('hero_combatants')->where('id', $combatantId)->update(['team' => $index + 1]);
                }
            }
        });

        DB::table('hero_battles')
            ->join('hero_combatants', 'hero_combatants.id', '=', 'hero_battles.winner_combatant_id')
            ->update(['hero_battles.winning_team' => DB::raw('hero_combatants.team')]);

        DB::table('hero_battles')->where('finished', false)->update(['finished' => true]);

        Schema::table('hero_combatants', function (Blueprint $table) {
            $table->dropColumn(['has_focus', 'shield', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('hero_combatants', function (Blueprint $table) {
            $table->boolean('has_focus')->default(false)->after('current_health');
            $table->unsignedInteger('shield')->default(0)->after('recover');
            $table->text('status')->nullable()->after('abilities');
            $table->dropForeign(['controller_dominion_id']);
            $table->dropColumn(['controller_dominion_id', 'team', 'template_key', 'effects', 'cooldowns', 'charges']);
        });

        Schema::table('heroes', function (Blueprint $table) {
            $table->dropColumn('last_pvp_reward_at');
        });

        Schema::table('hero_battle_actions', function (Blueprint $table) {
            $table->dropColumn('events');
        });

        Schema::table('hero_battles', function (Blueprint $table) {
            $table->dropColumn(['mode', 'encounter_key', 'seed', 'winning_team', 'effects', 'initial_state']);
        });
    }
}
