<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Position;
use App\Models\Event;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('logo')->default('img/logo_none.png');
            $table->string('website')->nullable();
            $table->tinyText('notes')->nullable();
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('club_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id');
            $table->boolean('managed');
            $table->string('name');
            $table->smallInteger('birth_year');
            $table->enum('rank', ['A', 'B', 'C', 'D'])->nullable();
            $table->string('website')->nullable();
            $table->tinyText('notes')->nullable();
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('club_team_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_team_id');
            $table->foreignId('season_id');
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_team_season_id');
            $table->foreignId('player_id');
            $table->tinyInteger('number')->nullable();
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('season');
            $table->smallInteger('year');
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('club_team_id');
            $table->enum('type', ['Cup', 'Friendly', 'League']);
            $table->string('division');
            $table->integer('place')->nullable();
            $table->integer('level')->nullable();
            $table->integer('total_levels')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->string('website')->nullable();
            $table->enum('status', ['A', 'C', 'D']); // A - Active, C - Cancelled, D - Done
            $table->tinyText('notes')->nullable();
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->smallInteger('birth_year');
            $table->string('photo')->default('img/photo_none.png');
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('player_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id');
            $table->foreignId('club_team_id');
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('player_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id');
            $table->foreignId('position_id');
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('position', 3);
        });

        $positions = [
            'ST',
            'LW',
            'LM',
            'RW',
            'RM',
            'CAM',
            'CM',
            'CDM',
            'LB',
            'RB',
            'CB',
            'G',
        ];
        foreach ($positions as $p)
        {
            $position = new Position();
 
            $position->position = $p;
            $position->save();
        }

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id');
            $table->foreignId('competition_id');
            $table->foreignId('location_id');
            $table->timestamp('date');
            $table->foreignId('home_team_id');
            $table->foreignId('away_team_id');
            $table->integer('home_team_score')->nullable();
            $table->integer('away_team_score')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('live')->default(false);
            $table->foreignId('formation_id')->nullable();
            $table->enum('status', ['S', 'C', 'D']); // S - Scheduled, C - Cancelled, D - Done
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->enum('players', ['7', '9', '11']);
            $table->string('name');
            $table->json('formation');
            $table->foreignId('created_user_id');
            $table->foreignId('updated_user_id');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event');
            $table->string('additional')->nullable();
        });

        $events = [
            [
                'event'      => 'goal',
                'additional' => 'assist',
            ],
            [
                'event'      => 'start',
                'additional' => 'position',
            ],
            [
                'event'      => 'sub_in',
                'additional' => 'position',
            ],
            [
                'event'      => 'sub_out',
                'additional' => null,
            ],
            [
                'event'      => 'goal_against',
                'additional' => null,
            ],
            [
                'event'      => 'shot_on_target',
                'additional' => null,
            ],
            [
                'event'      => 'shot_off_target',
                'additional' => null,
            ],
            [
                'event'      => 'tackle_won',
                'additional' => null,
            ],
            [
                'event'      => 'tackle_lost',
                'additional' => null,
            ],
            [
                'event'      => 'save',
                'additional' => null,
            ],
            [
                'event'      => 'shot_against',
                'additional' => null,
            ],
            [
                'event'      => 'corner_kick',
                'additional' => null,
            ],
            [
                'event'      => 'corner_kick_against',
                'additional' => null,
            ],
            [
                'event'      => 'offsides',
                'additional' => null,
            ],
            [
                'event'      => 'foul',
                'additional' => null,
            ],
            [
                'event'      => 'fouled',
                'additional' => null,
            ],
            [
                'event'      => 'yellow_card',
                'additional' => null,
            ],
            [
                'event'      => 'red_card',
                'additional' => null,
            ],
            [
                'event'      => 'penalty_goal',
                'additional' => null,
            ],
            [
                'event'      => 'penalty_on_target',
                'additional' => null,
            ],
            [
                'event'      => 'penalty_off_target',
                'additional' => null,
            ],
            [
                'event'      => 'free_kick_goal',
                'additional' => null,
            ],
            [
                'event'      => 'free_kick_on_target',
                'additional' => null,
            ],
            [
                'event'      => 'free_kick_off_target',
                'additional' => null,
            ],
            [
                'event'      => 'halftime',
                'additional' => null,
            ],
            [
                'event'      => 'fulltime',
                'additional' => null,
            ],
        ];
        foreach ($events as $e)
        {
            $event = new Event();
 
            $event->event      = $e['event'];
            $event->additional = $e['additional'];
            $event->save();
        }

        Schema::create('result_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id');
            $table->foreignId('player_id')->nullable();
            $table->time('time')->default('00:00:00');
            $table->foreignId('event_id');
            $table->string('additional')->nullable();
            $table->tinyInteger('xg')->nullable();
            $table->string('notes')->nullable();
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
        Schema::dropIfExists('soccer_tables');
    }
};
