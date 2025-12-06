<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValuablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('valuables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('round_id');
            $table->unsignedInteger('source_dominion_id');
            $table->unsignedInteger('target_dominion_id');
            $table->string('rarity');
            $table->string('type');
            $table->string('name');
            $table->unsignedInteger('spies_assigned')->default(0);
            $table->unsignedInteger('spy_hours')->nullable();
            $table->timestamp('investigation_started_at')->nullable();
            //$table->float('investigation_progress')->default(0);
            $table->timestamp('attempted_at')->nullable();
            $table->boolean('success')->default(false);
            $table->timestamp('sold_at')->nullable();
            $table->unsignedInteger('platinum_received')->nullable();
            $table->timestamps();

            $table->foreign('round_id')->references('id')->on('rounds');
            $table->foreign('source_dominion_id')->references('id')->on('dominions');
            $table->foreign('target_dominion_id')->references('id')->on('dominions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('valuables');
    }
}
