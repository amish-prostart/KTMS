<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('season')->nullable();
            $table->text('description')->nullable();
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'completed'])->default('upcoming');
            $table->unsignedSmallInteger('half_duration_seconds')->default(1200);
            $table->unsignedSmallInteger('raid_duration_seconds')->default(30);
            $table->unsignedTinyInteger('players_per_side')->default(7);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
