<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('raider_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('raiding_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('defending_team_id')->constrained('teams')->cascadeOnDelete();

            $table->unsignedInteger('raid_number');

            // 'successful'   -> raider scored at least one point
            // 'unsuccessful' -> raider was tackled / went out
            // 'empty'        -> nobody scored
            $table->enum('result', ['successful', 'unsuccessful', 'empty'])->default('empty');

            // Point split for this raid
            $table->unsignedTinyInteger('touch_points')->default(0);
            $table->unsignedTinyInteger('bonus_points')->default(0);
            $table->unsignedTinyInteger('raid_points')->default(0);       // total awarded to the raiding team
            $table->unsignedTinyInteger('defending_points')->default(0);  // total awarded to the defending team

            $table->boolean('is_bonus')->default(false);
            $table->boolean('is_super_raid')->default(false);
            $table->boolean('is_do_or_die')->default(false);

            $table->unsignedTinyInteger('half')->default(1);
            $table->unsignedSmallInteger('game_clock_at_event')->nullable();
            $table->unsignedSmallInteger('raid_duration_seconds')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['match_id', 'raid_number']);
            $table->index('raider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raids');
    }
};
