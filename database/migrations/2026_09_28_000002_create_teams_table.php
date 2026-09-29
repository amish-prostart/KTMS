<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('short_name', 10)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#1b4dff');
            $table->string('secondary_color', 7)->default('#ffffff');
            $table->string('coach_name')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'name']);
            $table->index('tournament_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
