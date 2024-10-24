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
use App\Models\ResultEvent;
use App\Enums\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

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

        // Find out which seasons this player was on the team for
        $seasonIds = Roster::with('clubTeamSeason')
            ->where('player_id', '=', $playerId)
            ->get()
            ->pluck('clubTeamSeason.season_id')
            ->toArray();

        // Get events from the games this user could have played in
        $resultEvents = ResultEvent::from('result_events as e')
            ->select('e.*', 'c.type as competition_type', 's.season', 's.year')
            ->join('results as r', 'e.result_id', '=', 'r.id')
            ->join('competitions as c', 'r.competition_id', '=', 'c.id')
            ->join('seasons as s', 'r.season_id', '=', 's.id')
            ->where(function (Builder $query) use ($playerId) {
                return $query->where('e.player_id', $playerId)
                    ->orWhere('e.additional', $playerId)
                    ->orWhere('e.event_id', Event::fulltime->value);
            })
            ->whereIn('r.season_id', $seasonIds)
            ->orderBy('e.time')
            ->orderBy('e.id')
            ->get()
            ->groupBy('result_id');

        $defaults = [
            'games'     => 0,
            'goals'     => 0,
            'assists'   => 0,
            'shots'     => 0,
            'shots_on'  => 0,
            'starts'    => 0,
            'playingTime' => [
                'possible_secs' => 0,
                'possible_mins' => 0,
                'seconds'       => 0,
                'minutes'       => 0,
                'spans'         => [],
            ],
            'position'  => [],
        ];

        $stats = [
            'seasons'  => [],
            'totals'   => [
                'all'      => $defaults,
                'League'   => $defaults,
                'Cup'      => $defaults,
                'Friendly' => $defaults,
            ],
        ];
        $charts = [
            'goals' => [
                'labels' => '',
                'data'   => '',
            ],
            'assists' => [
                'labels' => '',
                'data'   => '',
            ],
        ];

        foreach ($resultEvents as $resultId => $events)
        {
            $type   = $events[0]->competition_type;
            $season = $events[0]->season . ' ' . $events[0]->year;

            $fulltime = 0;

            $keyArr = [
                ['key1' => 'seasons', 'key2' => $season],
                ['key1' => 'totals',  'key2' => 'all'],
                ['key1' => 'totals',  'key2' => $type],
            ];

            if (!isset($stats['seasons'][$season]))
            {
                $stats['seasons'][$season] = $defaults;
            }

            $stats['seasons'][$season]['games']++;
            $stats['totals']['all']['games']++;
            $stats['totals'][$type]['games']++;

            foreach ($events as $e)
            {
                // goal/assist
                if (in_array($e->event_id, Event::getGoalValues()))
                {
                    if ($e->player_id == $playerId)
                    {
                        $stats['seasons'][$season]['goals']++;
                        $stats['seasons'][$season]['shots']++;
                        $stats['seasons'][$season]['shots_on']++;

                        $stats['totals']['all']['goals']++;
                        $stats['totals']['all']['shots']++;
                        $stats['totals']['all']['shots_on']++;

                        $stats['totals'][$type]['goals']++;
                        $stats['totals'][$type]['shots']++;
                        $stats['totals'][$type]['shots_on']++;
                    }
                    if ($e->additional == $playerId)
                    {
                        $stats['seasons'][$season]['assists']++;
                        $stats['totals']['all']['assists']++;
                        $stats['totals'][$type]['assists']++;
                    }
                }

                // full time
                if ($e->event_id == Event::fulltime->value)
                {
                    $secs = eventTimeToSeconds($e->time);

                    $fulltime = $e->time;

                    $stats['seasons'][$season]['playingTime']['possible_secs'] += $secs;
                    $stats['totals']['all']['playingTime']['possible_secs'] += $secs;
                    $stats['totals'][$type]['playingTime']['possible_secs'] += $secs;
                }

                // now skip any events where the player_id isn't this player
                if ($e->player_id != $playerId)
                {
                    continue;
                }

                // starter
                if ($e->event_id == Event::start->value)
                {
                    $span = [
                        'game'  => $resultId,
                        'start' => '00:00:00',
                        'end'   => null,
                    ];

                    $stats['seasons'][$season]['playingTime']['spans'][] = $span;
                    $stats['totals']['all']['playingTime']['spans'][] = $span;
                    $stats['totals'][$type]['playingTime']['spans'][] = $span;

                    $stats['seasons'][$season]['starts']++;
                    $stats['totals']['all']['starts']++;
                    $stats['totals'][$type]['starts']++;

                    $stats['seasons'][$season]['position'][ $e['additional'] ] = isset($stats['seasons'][$season]['position'][ $e['additional'] ]) 
                        ? ++$stats['seasons'][$season]['position'][ $e['additional'] ] : 1;

                    $stats['totals']['all']['position'][ $e['additional'] ] = isset($stats['totals']['all']['position'][ $e['additional'] ]) 
                        ? ++$stats['totals']['all']['position'][ $e['additional'] ] : 1;

                    $stats['totals'][$type]['position'][ $e['additional'] ] = isset($stats['totals'][$type]['position'][ $e['additional'] ]) 
                        ? ++$stats['totals'][$type]['position'][ $e['additional'] ] : 1;
                }

                // sub in
                if ($e->event_id == Event::sub_in->value)
                {
                    $span = [
                        'game'  => $resultId,
                        'start' => $e->time,
                        'end'   => null,
                    ];

                    $stats['seasons'][$season]['playingTime']['spans'][] = $span;
                    $stats['totals']['all']['playingTime']['spans'][] = $span;
                    $stats['totals'][$type]['playingTime']['spans'][] = $span;
                }

                // sub out
                if ($e->event_id == Event::sub_out->value)
                {
                    foreach ($keyArr as $keys)
                    {
                        $k1 = $keys['key1'];
                        $k2 = $keys['key2'];

                        foreach ($stats[$k1][$k2]['playingTime']['spans'] as $i => $span)
                        {
                            if ($span['end'] === null && $span['game'] == $resultId)
                            {
                                $stats[$k1][$k2]['playingTime']['spans'][$i]['end'] = $e->time;

                                $start = eventTimeToSeconds($span['start']);
                                $end   = eventTimeToSeconds($e->time);

                                $secs = $end - $start;

                                $stats[$k1][$k2]['playingTime']['seconds'] += $secs;
                            }
                        }
                    }
                }

                // shots on target
                if (in_array($e->event_id, Event::getShotOnTargetValues()))
                {
                    $stats['seasons'][$season]['shots']++;
                    $stats['seasons'][$season]['shots_on']++;
                    $stats['totals']['all']['shots']++;
                    $stats['totals']['all']['shots_on']++;
                    $stats['totals'][$type]['shots']++;
                    $stats['totals'][$type]['shots_on']++;
                }

                // shots
                if (in_array($e->event_id, Event::getShotOffTargetValues()))
                {
                    $stats['seasons'][$season]['shots']++;
                    $stats['totals']['all']['shots']++;
                    $stats['totals'][$type]['shots']++;
                }
            }

            // cleanup playing time for this game
            foreach ($keyArr as $keys)
            {
                $k1 = $keys['key1'];
                $k2 = $keys['key2'];

                foreach ($stats[$k1][$k2]['playingTime']['spans'] as $i => $span)
                {
                    if ($span['end'] === null && $span['game'] == $resultId)
                    {
                        $stats[$k1][$k2]['playingTime']['spans'][$i]['end'] = $fulltime;

                        $start = eventTimeToSeconds($span['start']);
                        $end   = eventTimeToSeconds($fulltime);

                        $secs = $end - $start;

                        $stats[$k1][$k2]['playingTime']['seconds'] += $secs;
                    }
                }

                // format everyones time in minutes
                $stats[$k1][$k2]['playingTime']['minutes']       = secondsToMinutes($stats[$k1][$k2]['playingTime']['seconds']);
                $stats[$k1][$k2]['playingTime']['possible_mins'] = secondsToMinutes($stats[$k1][$k2]['playingTime']['possible_secs']);
            }
        }

        foreach ($stats['seasons'] as $season => $data)
        {
            $charts['goals']['labels'] .= "'" . $season . "',";
            $charts['goals']['data']   .= "'" . $data['goals'] . "',";

            $charts['assists']['labels'] .= "'" . $season . "',";
            $charts['assists']['data']   .= "'" . $data['assists'] . "',";
        }

        return view('players.show', [
            'stats'  => $stats,
            'charts' => $charts,
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
