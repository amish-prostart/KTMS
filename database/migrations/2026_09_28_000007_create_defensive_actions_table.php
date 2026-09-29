<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('defensive_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            // A defensive action normally belongs to a raid, but the operator may also log a
            // standalone defensive event, so the raid link stays optional.
            $table->foreignId('raid_id')->nullable()->constrained('raids')->cascadeOnDelete();
            $table->foreignId('defender_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();

            $table->enum('action_type', [
                'tackle',
                'super_tackle',
                'ankle_hold',
                'thigh_hold',
                'block',
                'dash',
                'chain_tackle',
            ])->default('tackle');

            // Did the defender actually stop the raider?
            $table->boolean('is_successful')->default(false);
            $table->unsignedTinyInteger('points_awarded')->default(0);

            $table->unsignedTinyInteger('half')->default(1);
            $table->unsignedSmallInteger('game_clock_at_event')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['match_id', 'defender_id']);
            $table->index('raid_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('defensive_actions');
    }
};
