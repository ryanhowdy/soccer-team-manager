<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roster_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_team_season_id');
            $table->foreignId('result_id');
            $table->foreignId('player_id');
            $table->tinyInteger('number')->nullable();
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_guests');
    }
};
