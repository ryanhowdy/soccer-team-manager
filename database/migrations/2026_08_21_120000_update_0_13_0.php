<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds support for high school teams alongside club teams.
     */
    public function up(): void
    {
        // A high school is an organization type, not a team type: every team
        // under a school is a school team, so this lives on clubs and the teams
        // inherit it. Keeps a "club" team under a school impossible. (D1)
        Schema::table('clubs', function (Blueprint $table) {
            $table->enum('type', ['club', 'school'])
                ->default('club')
                ->after('name');
        });

        // Stable per-player value. Grade is never stored - it is derived per
        // season by gradeForSeason(), because grade changes every year. (D2)
        Schema::table('players', function (Blueprint $table) {
            $table->smallInteger('graduation_year')
                ->nullable()
                ->after('birth_year');
        });

        // Birth year is a club-team concept: a club team is a birth-year cohort,
        // a school team mixes ages. Now optional for both teams and players. (D3)
        Schema::table('club_teams', function (Blueprint $table) {
            $table->smallInteger('birth_year')->nullable()->change();
        });

        Schema::table('players', function (Blueprint $table) {
            $table->smallInteger('birth_year')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * NOTE: restoring birth_year to NOT NULL will fail if any school teams or
     * school players were created without one. Those rows have to be given a
     * birth year (or deleted) before this can roll back.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('graduation_year');
        });

        Schema::table('club_teams', function (Blueprint $table) {
            $table->smallInteger('birth_year')->nullable(false)->change();
        });

        Schema::table('players', function (Blueprint $table) {
            $table->smallInteger('birth_year')->nullable(false)->change();
        });
    }
};
