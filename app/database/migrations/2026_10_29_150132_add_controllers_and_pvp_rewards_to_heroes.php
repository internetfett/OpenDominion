<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Combatant controllers (the dominion piloting a combatant, which differs from the owner for
 * borrowed heroes) and the daily PvP experience reward.
 */
class AddControllersAndPvpRewardsToHeroes extends Migration
{
    public function up(): void
    {
        Schema::table('hero_combatants', function (Blueprint $table) {
            $table->unsignedInteger('controller_dominion_id')->nullable()->after('dominion_id');

            $table->foreign('controller_dominion_id')->references('id')->on('dominions');
        });

        Schema::table('heroes', function (Blueprint $table) {
            $table->timestamp('last_pvp_reward_at')->nullable()->after('experience');
        });

        DB::table('hero_combatants')
            ->whereNotNull('hero_id')
            ->update(['controller_dominion_id' => DB::raw('dominion_id')]);
    }

    public function down(): void
    {
        Schema::table('hero_combatants', function (Blueprint $table) {
            $table->dropForeign(['controller_dominion_id']);
            $table->dropColumn('controller_dominion_id');
        });

        Schema::table('heroes', function (Blueprint $table) {
            $table->dropColumn('last_pvp_reward_at');
        });
    }
}
