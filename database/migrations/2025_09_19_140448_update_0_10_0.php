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
        Schema::create('penalty_shootouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id'); // what game is this for
            $table->foreignId('first_team_id'); // which team shot first?
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penalty_shootout_id');
            $table->foreignId('player_id')->nullable();
            $table->foreignId('event_id'); // save (10), pk_goal (19), pk_on_target (20), pk_off_target (21)
            $table->boolean('against')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // dropp penalty_shootouts
        Schema::dropIfExists('penalty_shootouts');

        // drop penalties
        Schema::dropIfExists('penalties');
    }
};
