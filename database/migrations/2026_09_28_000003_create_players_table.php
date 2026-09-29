<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('jersey_number');
            $table->enum('role', ['raider', 'defender', 'all-rounder'])->default('all-rounder');
            $table->enum('position', [
                'left-corner',
                'left-in',
                'left-cover',
                'center',
                'right-cover',
                'right-in',
                'right-corner',
            ])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();
            $table->string('nationality')->nullable();
            $table->boolean('is_captain')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Jersey numbers must be unique inside a squad.
            $table->unique(['team_id', 'jersey_number']);
            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
