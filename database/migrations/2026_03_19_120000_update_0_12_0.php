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
        Schema::create('player_game_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('result_id');
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('created_user_id');
            $table->decimal('rating', 3, 1);
            $table->timestamps();

            $table->foreign('result_id')->references('id')->on('results');
            $table->foreign('player_id')->references('id')->on('players');
            $table->foreign('created_user_id')->references('id')->on('users');

            $table->unique(['result_id', 'player_id', 'created_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_game_ratings');
    }
};
