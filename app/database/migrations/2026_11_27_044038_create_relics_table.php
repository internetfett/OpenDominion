<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Relics replace the Valuables heist loop. A relic is owned from the moment it is found, and
 * rows stay after a sale or salvage as history. Valuables that were sold are copied over as
 * legacy history; the rest of the heist data (discoveries, investigations) is dropped.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('relics', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('round_id');
            $table->unsignedInteger('dominion_id');
            $table->unsignedInteger('previous_owner_dominion_id')->nullable();
            $table->string('category');
            $table->string('item_key')->nullable();
            $table->string('name');
            $table->string('rarity');
            $table->string('source');
            $table->timestamp('acquired_at');
            $table->boolean('is_bound')->default(false);
            $table->boolean('is_listed')->default(false);
            $table->unsignedInteger('sold_price')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->foreign('round_id')->references('id')->on('rounds');
            $table->foreign('dominion_id')->references('id')->on('dominions');
            $table->foreign('previous_owner_dominion_id')->references('id')->on('dominions');

            $table->index(['dominion_id', 'sold_at']);
            $table->index(['round_id', 'is_listed']);
            $table->index(['previous_owner_dominion_id', 'source', 'acquired_at']);
        });

        if (Schema::hasTable('valuables')) {
            DB::table('valuables')
                ->where('status', 'sold')
                ->orderBy('id')
                ->chunk(500, function ($valuables) {
                    DB::table('relics')->insert($valuables->map(fn ($valuable) => [
                        'round_id' => $valuable->round_id,
                        'dominion_id' => $valuable->source_dominion_id,
                        'category' => $valuable->type === 'relic' ? 'artifact' : $valuable->type,
                        'name' => $valuable->name,
                        'rarity' => $valuable->rarity,
                        'source' => 'legacy',
                        'acquired_at' => $valuable->stolen_at ?? $valuable->discovered_at,
                        'sold_price' => $valuable->sold_price,
                        'sold_at' => $valuable->updated_at ?? $valuable->discovered_at,
                        'created_at' => $valuable->created_at,
                        'updated_at' => $valuable->updated_at,
                    ])->all());
                });

            Schema::drop('valuables');
        }
    }

    public function down(): void
    {
        Schema::create('valuables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('round_id');
            $table->unsignedInteger('source_dominion_id');
            $table->unsignedInteger('target_dominion_id');
            $table->string('name');
            $table->string('rarity');
            $table->string('type');
            $table->string('status')->default('discovered');
            $table->integer('required_spy_hours');
            $table->integer('spies_assigned')->nullable();
            $table->timestamp('investigation_started_at')->nullable();
            $table->timestamp('investigation_ends_at')->nullable();
            $table->timestamp('stolen_at')->nullable();
            $table->timestamp('discovered_at');
            $table->boolean('is_listed')->default(false);
            $table->integer('sold_price')->nullable();
            $table->boolean('transferred')->default(false);
            $table->timestamps();

            $table->foreign('round_id')->references('id')->on('rounds');
            $table->foreign('source_dominion_id')->references('id')->on('dominions');
            $table->foreign('target_dominion_id')->references('id')->on('dominions');

            $table->index(['source_dominion_id', 'status']);
            $table->index(['target_dominion_id']);
        });

        Schema::dropIfExists('relics');
    }
};
