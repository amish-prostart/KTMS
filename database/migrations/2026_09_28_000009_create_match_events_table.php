<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('raid_id')->nullable()->constrained('raids')->nullOnDelete();

            $table->string('event_type', 40);
            $table->string('description');
            $table->integer('points')->default(0);

            $table->unsignedTinyInteger('half')->default(1);
            $table->unsignedSmallInteger('game_clock_at_event')->nullable();

            // Snapshot used to reverse the event when the operator hits undo.
            $table->json('payload')->nullable();
            $table->boolean('is_undone')->default(false);
            $table->timestamps();

            $table->index(['match_id', 'id']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_events');
    }
};
