<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\ClubTeam;
use App\Models\Season;
use App\Models\ClubTeamSeason;
use App\Models\Roster;
use App\Models\Position;
use App\Models\Result;

class PlayerController extends Controller
{
    /**
     * index
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get all managed teams
        $managedTeams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get();

        // Get all possible position
        $positions = Position::all();

        $activePlayers   = [];
        $inactivePlayers = [];

        // Get the players on the latest roster
        $latestSeason = Season::orderBy('id', 'desc')->first();

        $latestTeamSeasonIds = ClubTeamSeason::where('season_id', $latestSeason->id)
            ->get()
            ->pluck('id');

        $latestRoster = Roster::whereIn('club_team_season_id', $latestTeamSeasonIds)
            ->with('clubTeamSeason')
            ->with('player.positions')
            ->get();

        $activePlayerIds = [];

        foreach($latestRoster as $r)
        {
            $activePlayers[$r->clubTeamSeason->club_team_id][] = $r;

            $activePlayerIds[] = $r->player->id;
        }

        // Get the rest of the players
        $inactivePlayers = Player::orderBy('name')
            ->whereNotIn('id', $activePlayerIds)
            ->get()
            ->groupBy('club_team_id');

        return view('players.index', [
            'activePlayers'   => $activePlayers,
            'inactivePlayers' => $inactivePlayers,
            'positions'       => $positions,
            'managedTeams'    => $managedTeams,
        ]);
    }

    /**
     * show
     *
     * @return Illuminate\View\View
     */
    public function show($playerId, Request $request)
    {
        // Get the player
        $player = Player::find($playerId);

        // Find out which seasons this player was on the team for
        $seasonIds = Roster::with('clubTeamSeason')
            ->where('player_id', '=', $playerId)
            ->get()
            ->pluck('clubTeamSeason.season_id')
            ->toArray();

        // Get the games this user could have played in
        $results = Result::whereIn('season_id', $seasonIds)
            ->get();
        dump($results->toArray());

        $totals = [
            'all' => [
                'games'     => 0,
                'goals'     => 0,
                'assists'   => 0,
                'shots'     => 0,
                'shots_on'  => 0,
                'shots_off' => 0,
                'fouls'     => 0,
                'fouled'    => 0,
            ]
        ];

        dd($totals);
        return view('players.show', [
        ]);
    }
}
