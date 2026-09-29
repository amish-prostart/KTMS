<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('matches_played')->default(0);

            // Raiding
            $table->unsignedInteger('total_raids')->default(0);
            $table->unsignedInteger('successful_raids')->default(0);
            $table->unsignedInteger('unsuccessful_raids')->default(0);
            $table->unsignedInteger('empty_raids')->default(0);
            $table->unsignedInteger('raid_points')->default(0);
            $table->unsignedInteger('touch_points')->default(0);
            $table->unsignedInteger('bonus_points')->default(0);
            $table->unsignedInteger('super_raids')->default(0);
            $table->unsignedInteger('do_or_die_raids')->default(0);
            $table->unsignedInteger('do_or_die_conversions')->default(0);

            // Defending
            $table->unsignedInteger('total_defenses')->default(0);
            $table->unsignedInteger('successful_defenses')->default(0);
            $table->unsignedInteger('failed_defenses')->default(0);
            $table->unsignedInteger('defense_points')->default(0);
            $table->unsignedInteger('super_tackles')->default(0);

            // Combined
            $table->unsignedInteger('total_points')->default(0);
            $table->timestamps();

            // One aggregate row per player per tournament.
            $table->unique(['player_id', 'tournament_id']);
            $table->index('tournament_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_statistics');
    }
};
