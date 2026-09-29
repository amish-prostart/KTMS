<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_starter')->default(false);
            $table->boolean('is_on_court')->default(false);
            $table->enum('status', ['on_court', 'out', 'bench'])->default('bench');
            $table->timestamps();

            $table->unique(['match_id', 'player_id']);
            $table->index(['match_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_player');
    }
};
