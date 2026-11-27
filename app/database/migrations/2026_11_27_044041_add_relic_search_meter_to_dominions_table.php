<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The relic Search meter fills each tick while Search is on, the same way the Fireball and
 * Lightning Bolt meters change: precalculated into dominion_tick and applied in bulk.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('dominions', function (Blueprint $table) {
            $table->decimal('relic_search_meter', 6, 2)->after('lightning_bolt_meter')->default(0);
        });

        Schema::table('dominion_tick', function (Blueprint $table) {
            $table->decimal('relic_search_meter', 6, 2)->after('lightning_bolt_meter')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('dominions', function (Blueprint $table) {
            $table->dropColumn('relic_search_meter');
        });

        Schema::table('dominion_tick', function (Blueprint $table) {
            $table->dropColumn('relic_search_meter');
        });
    }
};
