<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per resolved turn holding its ordered structured events, replacing the per-action
 * text log. Existing text log rows cannot be turned into events, so they are dropped.
 */
class CreateHeroBattleTurnsTable extends Migration
{
    public function up(): void
    {
        Schema::create('hero_battle_turns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hero_battle_id')->unsigned();
            $table->integer('turn');
            $table->mediumText('events');
            $table->timestamps();

            $table->unique(['hero_battle_id', 'turn']);
            $table->foreign('hero_battle_id')->references('id')->on('hero_battles')->cascadeOnDelete();
        });

        Schema::dropIfExists('hero_battle_actions');
    }

    public function down(): void
    {
        Schema::create('hero_battle_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hero_battle_id')->unsigned();
            $table->integer('combatant_id')->unsigned()->nullable();
            $table->integer('target_combatant_id')->unsigned()->nullable();
            $table->integer('turn');
            $table->string('action');
            $table->integer('damage');
            $table->integer('health');
            $table->text('description');
            $table->text('events')->nullable();
            $table->timestamps();

            $table->foreign('hero_battle_id')->references('id')->on('hero_battles');
            $table->foreign('combatant_id')->references('id')->on('hero_combatants');
            $table->foreign('target_combatant_id')->references('id')->on('hero_combatants');
        });

        Schema::dropIfExists('hero_battle_turns');
    }
}
