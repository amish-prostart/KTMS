<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedInteger('match_number')->nullable();
            $table->string('round')->nullable();
            $table->string('venue')->nullable();
            $table->dateTime('scheduled_at')->nullable();

            $table->enum('status', ['scheduled', 'live', 'half_time', 'completed'])->default('scheduled');

            // Scoreboard
            $table->unsignedInteger('home_score')->default(0);
            $table->unsignedInteger('away_score')->default(0);

            // Players currently standing on the mat for each side.
            $table->unsignedTinyInteger('home_players_on_court')->default(7);
            $table->unsignedTinyInteger('away_players_on_court')->default(7);
            $table->unsignedTinyInteger('players_per_side')->default(7);

            // Half tracking
            $table->unsignedTinyInteger('current_half')->default(1);
            $table->unsignedSmallInteger('half_duration_seconds')->default(1200);

            /*
             * Game clock. `game_clock_remaining_seconds` is the authoritative value at the
             * instant captured by `game_timer_updated_at`. While `game_timer_running` is true
             * the effective remaining time is (remaining - (now - updated_at)), which lets any
             * number of browsers render the same countdown without drifting.
             */
            $table->unsignedSmallInteger('game_clock_remaining_seconds')->default(1200);
            $table->boolean('game_timer_running')->default(false);
            $table->timestamp('game_timer_updated_at')->nullable();

            // Raid clock, same anchor technique as the game clock.
            $table->unsignedSmallInteger('raid_duration_seconds')->default(30);
            $table->unsignedSmallInteger('raid_clock_remaining_seconds')->default(30);
            $table->boolean('raid_timer_running')->default(false);
            $table->timestamp('raid_timer_updated_at')->nullable();

            // Raid state
            $table->boolean('is_raid_active')->default(false);
            $table->foreignId('raiding_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedInteger('raid_count')->default(0);

            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tournament_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
