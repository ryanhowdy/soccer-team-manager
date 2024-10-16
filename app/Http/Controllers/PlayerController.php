<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\ClubTeam;
use App\Models\Season;
use App\Models\ClubTeamSeason;
use App\Models\Roster;
use App\Models\Position;
use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        if ($managedTeams->count() <= 0)
        {
            return redirect()->route('teams.index')->withErrors(['You must create at least 1 managed team.']);
        }

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
            ->with('player.teams')
            ->with('player.positions')
            ->get();

        $activePlayerTeamIds = [];

        foreach($latestRoster as $r)
        {
            $activePlayers[$r->clubTeamSeason->club_team_id][] = $r;

            foreach ($r->player->teams as $pt)
            {
                if ($pt->club_team_id == $r->clubTeamSeason->club_team_id)
                {
                    $activePlayerTeamIds[] = $pt->id;
                }
            }
        }

        // Get the rest of the players
        $inactivePlayers = PlayerTeam::from('player_teams as pt')
            ->select('p.*', 'pt.club_team_id', 't.name as team_name')
            ->join('players as p', 'pt.player_id', '=', 'p.id')
            ->join('club_teams as t', 'pt.club_team_id', '=', 't.id')
            ->whereNotIn('pt.id', $activePlayerTeamIds)
            ->orderBy('name')
            ->get()
            ->groupBy('club_team_id');

        $allPlayers = collect([]);
        foreach ($activePlayers as $clubTeamId => $players)
        {
            foreach ($players as $p)
            {
                $allPlayers->push($p->player);
            }
        }
        foreach ($inactivePlayers as $clubTeamId => $players)
        {
            foreach ($players as $p)
            {
                $allPlayers->push($p);
            }
        }

        $allPlayers = $allPlayers->unique('id')->values();
        $allPlayers = $allPlayers->sortBy('name')->values();

        return view('players.index', [
            'activePlayers'    => $activePlayers,
            'inactivePlayers'  => $inactivePlayers,
            'positions'        => $positions,
            'managedTeams'     => $managedTeams,
            'action'           => route('players.store'),
            'allPlayers'       => $allPlayers,
        ]);
    }

    /**
     * store 
     * 
     * @param Request $request 
     * @return null
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_id'    => 'nullable|required_without:name|exists:players,id',
            'name'         => 'nullable|required_without:player_id|string|max:255|unique:players,name',
            'nickname'     => 'nullable|string|max:255',
            'birth_year'   => 'nullable|required_without:player_id|date_format:Y',
            'club_team_id' => 'required|exists:club_teams,id',
            'photo'        => 'nullable|image',
        ]);

        DB::beginTransaction();

        try
        {
            $playerId = null;

            // Existing player
            if ($request->filled('player_id'))
            {
                $playerId = $request->player_id;
            }
            // Create new player
            else
            {
                $player = new Player;

                $player->name            = $request->name;
                $player->birth_year      = $request->birth_year;
                $player->created_user_id = Auth()->user()->id;
                $player->updated_user_id = Auth()->user()->id;

                if ($request->filled('nickname'))
                {
                    $player->nickname = $request->nickname;
                }
                if ($request->has('photo'))
                {
                    $file = $request->file('photo');

                    // upload the logo to the filesystem
                    $path = $file->store('photos', 'public');

                    // set the logo url in the db
                    $player->photo = 'storage/' . $path;
                }

                $player->save();

                $playerId = $player->id;
            }

            // Create new player team
            $playerTeam = new PlayerTeam;

            $playerTeam->player_id       = $playerId;
            $playerTeam->club_team_id    = $request->club_team_id;
            $playerTeam->created_user_id = Auth()->user()->id;
            $playerTeam->updated_user_id = Auth()->user()->id;

            $playerTeam->save();

            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollback();
            dd($e->getMessage());
        }

        return redirect()->route('players.index');
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
        dd($player->toArray());

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

    /**
     * edit 
     * 
     * @param Player $player 
     * @param Request $request 
     * @return Illuminate\View\View
     */
    public function edit(Player $player, Request $request)
    {
        $teams = DB::table('rosters as r')
            ->select('t.*', 'r.number', 's.season', 's.year')
            ->join('club_team_seasons as cts', 'r.club_team_season_id', '=', 'cts.id')
            ->join('club_teams as t', 'cts.club_team_id', '=', 't.id')
            ->join('seasons as s', 'cts.season_id', '=', 's.id')
            ->where('r.player_id', $player->id)
            ->orderBy('s.year', 'asc')
            ->get();

        return view('players.edit', [
            'player' => $player,
            'teams'  => $teams,
        ]);
    }

    /**
     * edit 
     * 
     * @param Player $player 
     * @param Request $request 
     * @return Illuminate\View\View
     */
    public function update(Player $player, Request $request)
    {
        $validated = $request->validate([
            'name'       => [
                'required',
                'string',
                'max:255',
                Rule::unique('players', 'name')->ignore($player),
            ],
            'nickname'   => 'nullable|string|max:255',
            'birth_year' => 'required|date_format:Y',
            'photo'      => 'nullable|image',
            'managed'    => 'nullable|integer',
        ]);

        $player->name       = $request->name;
        $player->birth_year = $request->birth_year;
        $player->managed    = $request->has('managed')     ? 1                  : 0;
        $player->nickname   = $request->filled('nickname') ? $request->nickname : null;

        if ($request->has('photo'))
        {
            $file = $request->file('photo');

            // upload the logo to the filesystem
            $path = $file->store('photos', 'public');

            // set the logo url in the db
            $player->photo = 'storage/' . $path;
        }

        $player->save();

        return redirect()->route('players.index');
    }
}
