<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use App\Models\PlayerTeam;
use App\Models\ClubTeamSeason;
use App\Models\Roster;

class RosterController extends Controller
{
    /**
     * index
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get all the rosters grouped by team, then by season
        $rosters = ClubTeamSeason::from('club_team_seasons as cts')
            ->select('p.id as player_id', 'p.name', 'p.nickname', 'r.id as roster_id', 'r.number', 'cts.id as club_team_season_id', 't.id as club_team_id', 't.name as team_name', DB::raw("concat(s.season, ' ', s.year) as 'season_year'"))
            ->join('club_teams as t', 'cts.club_team_id', '=', 't.id')
            ->join('seasons as s', 'cts.season_id', '=', 's.id')
            ->leftJoin('rosters as r', 'r.club_team_season_id', '=', 'cts.id')
            ->leftJoin('players as p', 'r.player_id', '=', 'p.id')
            ->orderBy('s.id')
            ->get()
            ->groupBy(['season_year', 'team_name'], preserveKeys: true);

        $playersByTeam = PlayerTeam::from('player_teams as pt')
            ->select('p.*', 'pt.club_team_id', 't.name as team_name')
            ->join('players as p', 'pt.player_id', '=', 'p.id')
            ->join('club_teams as t', 'pt.club_team_id', '=', 't.id')
            ->orderBy('name')
            ->get()
            ->keyBy(function ($item) {
                return $item->id . '-' . $item->club_team_id;
            })
            ->groupBy('team_name', preserveKeys: true);

        if ($playersByTeam->isEmpty())
        {
            return redirect()->route('players.index')->withErrors(['You must create at least 1 player fore every managed team.']);
        }

        $clubTeamSeasonLkup = ClubTeamSeason::from('club_team_seasons as cts')
            ->select('cts.id', DB::raw("concat(s.season, ' ', s.year, '-', t.name) as 'name'"))
            ->join('club_teams as t', 'cts.club_team_id', '=', 't.id')
            ->join('seasons as s', 'cts.season_id', '=', 's.id')
            ->pluck('id', 'name')
            ->toArray();

        $prevSeason                   = null;
        $playersBySeasonTeam          = [];
        $availablePlayersBySeasonTeam = [];

        foreach($rosters as $seasonName => $teams)
        {
            foreach($teams as $teamName => $players)
            {
                $playersBySeasonTeam[$seasonName][$teamName] = [];

                $prevRoster = [];
                if (isset($prevSeason[$teamName]))
                {
                    $prevRoster = isset($rosters[ $prevSeason[$teamName] ]) ? $rosters[ $prevSeason[$teamName] ][$teamName] : null;
                }

                $availablePlayers = clone $playersByTeam[$teamName];

                // Players added this roster
                foreach($players->sortBy('name') as $player)
                {
                    if (is_null($player->player_id))
                    {
                        continue;
                    }

                    unset($availablePlayers[ $player->player_id . '-' . $player->club_team_id ]);

                    $class = '';

                    if (empty($prevRoster))
                    {
                        $class = 'add';
                    }
                    else
                    {
                        $playerWasOnPreviousRoster = 0;

                        foreach ($prevRoster as $prevPlayer)
                        {
                            if ($prevPlayer->player_id === $player->player_id)
                            {
                                $playerWasOnPreviousRoster++;
                                break;
                            }
                        }

                        // if the player wasn't on the previous roster then he was added
                        if (!$playerWasOnPreviousRoster)
                        {
                            $class = 'add';
                        }
                    }

                    $playersBySeasonTeam[$seasonName][$teamName][$player->player_id] = [
                        'id'                  => $player->player_id,
                        'club_team_season_id' => $player->club_team_season_id,
                        'name'                => $player->name,
                        'roster_id'           => $player->roster_id,
                        'number'              => $player->number,
                        'class'               => $class,
                    ];
                }

                $availablePlayersBySeasonTeam[$seasonName][$teamName] = $availablePlayers->toArray();

                // Players removed this roster
                foreach ($prevRoster as $prevPlayer)
                {
                    $playerOnCurrentRoster = 0;
                    foreach($players->sortBy('name') as $player)
                    {
                        if ($prevPlayer->player_id === $player->player_id)
                        {
                            $playerOnCurrentRoster++;
                            break;
                        }
                    }

                    // if the player isn't on the current roster they he was removed
                    if (!$playerOnCurrentRoster)
                    {
                        $playersBySeasonTeam[$seasonName][$teamName][$prevPlayer->player_id] = [
                            'id'                  => $prevPlayer->player_id,
                            'club_team_season_id' => $player->club_team_season_id,
                            'name'                => $prevPlayer->name,
                            'roster_id'           => $prevPlayer->roster_id,
                            'number'              => null,
                            'class'               => 'rem',
                        ];
                    }
                }

                $prevSeason[$teamName] = $seasonName;
            }
        }

        $playersBySeasonTeam = array_reverse($playersBySeasonTeam, true);

        return view('rosters.index', [
            'playersBySeasonTeam'          => $playersBySeasonTeam,
            'availablePlayersBySeasonTeam' => $availablePlayersBySeasonTeam,
            'clubTeamSeasonLkup'           => $clubTeamSeasonLkup,
        ]);
    }

    /**
     * update
     * 
     * @param Roster $id 
     * @param Request $request 
     * @return Illuminate\View\View
     */
    public function update(Roster $roster, Request $request)
    {
        $validated = $request->validate([
            'club_team_season_id' => 'required|exists:club_team_seasons,id',
            'player_id'           => 'required|integer|exists:players,id',
            'number' => [
                'nullable',
                'integer',
                Rule::unique('rosters', 'number')
                    ->where(fn (Builder $query) => $query->where('club_team_season_id', $request->club_team_season_id)),
            ],
        ]);

        $roster->club_team_season_id = $request->club_team_season_id;
        $roster->player_id           = $request->player_id;
        $roster->updated_user_id     = Auth()->user()->id;

        if ($request->filled('number'))
        {
            $roster->number = $request->number;
        }

        $roster->save();

        return redirect()->route('rosters.index');
    }
}
