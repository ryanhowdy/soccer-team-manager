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
        // add club_team_season_id column to result table
        Schema::table('results', function (Blueprint $table) {
            $table->after('season_id', function (Blueprint $t) {
                $t->foreignId('club_team_season_id');
            });

            $table->dropColumn('season_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop club_team_season_id column
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn('club_team_season_id');
        });
    }
};
