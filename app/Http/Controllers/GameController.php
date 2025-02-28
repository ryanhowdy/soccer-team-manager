<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\ClubTeam;
use App\Models\ClubTeamSeason;
use App\Models\Competition;
use App\Models\Location;
use App\Models\ResultEvent;
use App\Enums\Event as EnumEvent;
use App\Enums\CompetitionStatus;
use App\Enums\ResultStatus;
use Carbon\Carbon;

class GameController extends Controller
{
    /**
     * index
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get all seasons
        $seasons = Season::all()->keyBy('id');

        // Get all non managed teams, group them by club
        $teams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->whereNot('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get()
            ->keyBy('id');

        $teamsByClub   = [];
        $teamIdsByClub = [];
        foreach ($teams as $team)
        {
            $teamsByClub[$team->club_name][] = $team->toArray();
            $teamIdsByClub[$team->club_id][] = $team->id;
        }

        // Get only managed teams
        $managedTeams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get();

        // Get all active competitions grouped by type
        $competitions = Competition::where('status', CompetitionStatus::Active)
            ->orderBy('started_at', 'desc')
            ->get()
            ->groupBy('type');

        // Get all locations
        $allLocations = Location::orderBy('name')
            ->get();

        // Get 5 most recently created locations
        $recentLocations = Location::orderByDesc('created_at')
            ->limit(5)
            ->get();

        $locations = collect([
            'Recent' => $recentLocations,
            'All'    => $allLocations,
        ]);

        // Any filters
        $selected = [
            'seasonId' => $request->has('filter-seasons') ? $request->input('filter-seasons') : $seasons->keys()->last(),
            'clubId'   => $request->has('filter-clubs')   ? $request->input('filter-clubs')   : null,
            'teamId'   => $request->has('filter-teams')   ? $request->input('filter-teams')   : null,
        ];

        $clubTeamSeasonIds = [];
        $seasonIds         = [ $seasons->keys()->last() ];
        $clubIds           = [];
        $teamIds           = [];

        if ($request->has('filter-seasons'))
        {
            if (is_null($request->input('filter-seasons')))
            {
                $seasonIds = $seasons->keys();
            }
            else
            {
                $seasonIds = [ $request->input('filter-seasons') ];
            }
        }
        if ($request->has('filter-clubs') && !is_null($request->input('filter-clubs')))
        {
            $clubIds = $teamIdsByClub[ $request->input('filter-clubs') ];
        }
        if ($request->has('filter-teams') && !is_null($request->input('filter-teams')))
        {
            $teamIds = [ $request->input('filter-teams') ];
        }

        // Turn the season_id into a club_team_season_id
        $clubTeamSeasonIds = ClubTeamSeason::whereIn('season_id', $seasonIds)
            ->get()
            ->pluck('id')
            ->toArray();

        // Get all the results
        $query = Result::query()
            ->where('status', ResultStatus::Scheduled)
            ->orWhere(function (Builder $q) use ($clubTeamSeasonIds, $clubIds, $teamIds) {
                $q->where('status', ResultStatus::Done)
                  ->whereIn('club_team_season_id', $clubTeamSeasonIds);

                if ($clubIds)
                {
                    $q->where(function (Builder $b) use ($clubIds) {
                        $b->whereIn('home_team_id', $clubIds)
                          ->orWhereIn('away_team_id', $clubIds);
                    });
                }
                else if ($teamIds)
                {
                    $q->where(function (Builder $b) use ($teamIds) {
                        $b->whereIn('home_team_id', $teamIds)
                          ->orWhereIn('away_team_id', $teamIds);
                    });
                }
            });

        $results = $query->orderByDesc('date')
            ->get()
            ->groupBy('status');

        // Scheduled game should show oldest first
        if (isset($results['S']))
        {
            $results['S'] = $results['S']->reverse();
        }

        return view('games', [
            'selectedSeason' => $selected['seasonId'],
            'selectedClub'   => $selected['clubId'],
            'selectedTeam'   => $selected['teamId'],
            'seasons'        => $seasons,
            'teamsByClub'    => $teamsByClub,
            'results'        => $results,
            'action'         => route('games.store'),
            'competitions'   => $competitions,
            'locations'      => $locations,
            'managedTeams'   => $managedTeams,
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
            'season_id'         => 'required|exists:seasons,id',
            'competition_id'    => 'required|exists:competitions,id',
            'location_id'       => 'required|exists:locations,id',
            'date'              => 'required|date_format:Y-m-d',
            'time'              => 'required|date_format:H:i',
            'my_team_id'        => 'required|exists:club_teams,id',
            'my_home_away'      => 'required|in:home,away',
            'opponent_team_id'  => 'required|exists:club_teams,id',
        ]);

        // Get club_team_session_id for this season and team
        $clubTeamSeason = ClubTeamSeason::where('season_id', '=', $request->season_id)
            ->where('club_team_id', '=', $request->my_team_id)
            ->first();

        // Create new result
        $result = new Result;

        $datetime = $request->date . ' ' . $request->time;

        $date = Carbon::createFromFormat('Y-m-d H:i', $datetime, config('stm.timezone_display'));
        $date->tz('UTC');

        $result->club_team_season_id = $clubTeamSeason->id;
        $result->competition_id      = $request->competition_id;
        $result->location_id         = $request->location_id;
        $result->date                = $date;
        $result->status              = ResultStatus::Scheduled;
        $result->created_user_id     = Auth()->user()->id;
        $result->updated_user_id     = Auth()->user()->id;

        $result->home_team_id = $request->my_team_id;
        $result->away_team_id = $request->opponent_team_id;

        if ($request->my_home_away == 'away')
        {
            $result->home_team_id = $request->opponent_team_id;
            $result->away_team_id = $request->my_team_id;
        }

        $result->save();

        return redirect()->route('games.index');
    }

    /**
     * show
     *
     * @return Illuminate\View\View
     */
    public function show($gameId)
    {
        // Get the game info
        $result = Result::with('formation')
            ->with('homeTeam.club')
            ->with('awayTeam.club')
            ->find($gameId);

        // If the game isn't marked as 'Done', redirect to preview page
        if ($result->status != 'D')
        {
            return redirect()->route('games.preview', ['id' => $gameId]);
        }

        // Get all the events for this game
        $resultEvents = ResultEvent::where('result_id', $gameId)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        // Get all the players who are rostered for this game
        $players = Player::select('players.*', 'rosters.number')
            ->with('positions')
            ->orderBy('name')
            ->join('rosters', function (JoinClause $join) use ($result) {
                $join->on('rosters.player_id', '=', 'players.id')
                    ->where('club_team_season_id', $result->club_team_season_id);
            })
            ->get()
            ->keyBy('id');

        $managedPlayerIds = $players->where('managed', 1)->pluck('name', 'id')->toArray();

        $goodGuys = $result->homeTeam->managed ? 'home' : 'away';
        $badGuys  = $goodGuys == 'home'        ? 'away' : 'home';

        $goodGuysId = $result->{$goodGuys . '_team_id'};
        $badGuysId  = $result->{$badGuys . '_team_id'};

        // Get all the head to head games
        $head2HeadResults = Result::where('status', 'D')
            ->where(function (Builder $q) use ($goodGuysId) {
                $q->where('home_team_id', $goodGuysId)
                    ->orWhere('away_team_id', $goodGuysId);
            })
            ->where(function (Builder $q) use ($badGuysId) {
                $q->where('home_team_id', $badGuysId)
                    ->orWhere('away_team_id', $badGuysId);
            })
            ->orderBy('date', 'desc')
            ->get();

        // Figure out the chart data based on the head 2 head results
        $chartData = \Chart::getData(['wdl'], $goodGuysId, $head2HeadResults);

        $goals = [
            'home' => [],
            'away' => [],
        ];

        $stats = [
            'players' => [],
            'home'    => [
                'goals'        => 0,
                'shots'        => 0,
                'shots_on'     => 0,
                'shots_off'    => 0,
                'corners'      => 0,
                'fouls'        => 0,
                'yellow_cards' => 0,
                'red_cards'    => 0,
                'offsides'     => 0,
                'xg'           => 0,
                'xgs'          => '',
            ],
            'away' => [
                'goals'        => 0,
                'shots'        => 0,
                'shots_on'     => 0,
                'shots_off'    => 0,
                'corners'      => 0,
                'fouls'        => 0,
                'yellow_cards' => 0,
                'red_cards'    => 0,
                'offsides'     => 0,
                'xg'           => 0,
                'xgs'          => '',
            ],
        ];

        $starters    = [];
        $playingTime = [];
        $fulltime    = 0;

        $havePlayingTimeStats = 0;

        $playerStatsWeTrack = [
            EnumEvent::goal->value,
            EnumEvent::shot_on_target->value,
            EnumEvent::shot_off_target->value,
            EnumEvent::tackle_won->value,
            EnumEvent::tackle_lost->value,
            EnumEvent::offsides->value,
            EnumEvent::penalty_goal->value,
            EnumEvent::penalty_on_target->value,
            EnumEvent::penalty_off_target->value,
            EnumEvent::free_kick_goal->value,
            EnumEvent::free_kick_on_target->value,
            EnumEvent::free_kick_off_target->value,
        ];

        $goalEvents = [
            EnumEvent::goal->value, 
            EnumEvent::penalty_goal->value, 
            EnumEvent::free_kick_goal->value
        ];
        $shotOnEvents = [
            EnumEvent::shot_on_target->value,
            EnumEvent::penalty_on_target->value,
            EnumEvent::free_kick_on_target->value
        ];
        $shotOffEvents = [
            EnumEvent::shot_off_target->value,
            EnumEvent::penalty_off_target->value,
            EnumEvent::free_kick_off_target->value,
        ];

        foreach($resultEvents as $e)
        {
            // keep track of who this event is for (us/good or them/bad)
            $usOrThem = $e->against == 1 ? $badGuys : $goodGuys;

            // create a record for any players with stats that we track
            if (!$e->against && in_array($e->event_id, $playerStatsWeTrack))
            {
                if (!isset($stats['players'][$e->player_id]))
                {
                    $stats['players'][$e->player_id] = [
                        'player'   => $e->player,
                        'goals'    => 0,
                        'assists'  => 0,
                        'shots'    => 0,
                        'shots_on' => 0,
                        'offsides' => 0,
                        'tackles'  => 0,
                    ];
                }
                if (in_array($e->event_id, $goalEvents) && $e->additional && !isset($stats['players'][$e->additional]))
                {
                    $stats['players'][$e->additional] = [
                        'player'   => $e->additionalPlayer,
                        'goals'    => 0,
                        'assists'  => 0,
                        'shots'    => 0,
                        'shots_on' => 0,
                        'offsides' => 0,
                        'tackles'  => 0,
                    ];
                }
            }

            if ($e->event_id == EnumEvent::start->value)
            {
                $starters[$e->player_id] = $e->additional;

                $playingTime[$e->player_id] = [
                    'player'   => $e->player,
                    'starter'  => true,
                    'seconds'  => 0,
                    'spans' => [
                        [
                            'start' => '00:00:00',
                            'end'   => null,
                        ],
                    ],
                ];
            }
            if ($e->event_id == EnumEvent::sub_out->value)
            {
                $havePlayingTimeStats = 1;

                foreach($playingTime[$e->player_id]['spans'] as $i => $span)
                {
                    if ($span['end'] === null)
                    {
                        $playingTime[$e->player_id]['spans'][$i]['end'] = $e->time;

                        $start = eventTimeToSeconds($span['start']);
                        $end   = eventTimeToSeconds($e->time);

                        $secs = $end - $start;

                        $playingTime[$e->player_id]['seconds'] += $secs;
                    }
                }
            }
            if ($e->event_id == EnumEvent::sub_in->value)
            {
                $havePlayingTimeStats = 1;

                if (isset($playingTime[$e->player_id]))
                {
                    $playingTime[$e->player_id]['spans'][] = [
                        'start' => $e->time,
                        'end'   => null,
                    ];
                }
                else
                {
                    $playingTime[$e->player_id] = [
                        'player'   => $e->player,
                        'starter'  => false,
                        'seconds'  => 0,
                        'spans' => [
                            [
                                'start' => $e->time,
                                'end'   => null,
                            ],
                        ],
                    ];
                }
            }
            if ($e->event_id == EnumEvent::fulltime->value)
            {
                $fulltime = $e->time;
            }
            if (in_array($e->event_id, $goalEvents))
            {
                $stats[$usOrThem]['goals']++;
                $stats[$usOrThem]['shots']++;
                $stats[$usOrThem]['shots_on']++;

                $goals[$usOrThem][] = $e;

                if ($e->xg)
                {
                    $stats[$usOrThem]['xg'] += number_format($e->xg / 10, 1);
                    $stats[$usOrThem]['xgs'] .= number_format($e->xg / 10, 1) . " | ";
                }

                if (!$e->against)
                {
                    $stats['players'][$e->player_id]['goals']++;
                    $stats['players'][$e->player_id]['shots']++;
                    $stats['players'][$e->player_id]['shots_on']++;

                    if ($e->additional)
                    {
                        $stats['players'][$e->additional]['assists']++;
                    }
                }
            }
            if (in_array($e->event_id, $shotOnEvents))
            {
                $stats[$usOrThem]['shots']++;
                $stats[$usOrThem]['shots_on']++;

                if ($e->xg)
                {
                    $stats[$usOrThem]['xg'] += number_format($e->xg / 10, 1);
                    $stats[$usOrThem]['xgs'] .= number_format($e->xg / 10, 1) . " | ";
                }

                if (!$e->against)
                {
                    $stats['players'][$e->player_id]['shots']++;
                    $stats['players'][$e->player_id]['shots_on']++;
                }
            }
            if (in_array($e->event_id, $shotOffEvents))
            {
                $stats[$usOrThem]['shots']++;
                $stats[$usOrThem]['shots_off']++;

                if ($e->xg)
                {
                    $stats[$usOrThem]['xg'] += number_format($e->xg / 10, 1);
                    $stats[$usOrThem]['xgs'] .= number_format($e->xg / 10, 1) . " | ";
                }

                if (!$e->against)
                {
                    $stats['players'][$e->player_id]['shots']++;
                }
            }
            if ($e->event_id == EnumEvent::corner_kick->value)
            {
                $stats[$usOrThem]['corners']++;
            }
            if ($e->event_id == EnumEvent::foul->value)
            {
                $stats[$goodGuys]['fouls']++;
            }
            if ($e->event_id == EnumEvent::fouled->value)
            {
                $stats[$badGuys]['fouls']++;
            }
            if ($e->event_id == EnumEvent::yellow_card->value)
            {
                $stats[$usOrThem]['yellow_cards']++;
            }
            if ($e->event_id == EnumEvent::red_card->value)
            {
                $stats[$usOrThem]['red_cards']++;
            }
            if ($e->event_id == EnumEvent::tackle_won->value && !$e->against)
            {
                $stats['players'][$e->player_id]['tackles']++;
            }
            if ($e->event_id == EnumEvent::offsides->value)
            {
                $stats[$usOrThem]['offsides']++;

                if (!$e->against)
                {
                    $stats['players'][$e->player_id]['offsides']++;
                }
            }
            if ($e->event_id == EnumEvent::save->value)
            {
                // NOTE: save is weird, because it is a good guy stat, but also counts as a bad guy shot/shot_on
                $stats[$badGuys]['shots']++;
                $stats[$badGuys]['shots_on']++;

                if ($e->xg)
                {
                    $stats[$badGuys]['xg'] += number_format($e->xg / 10, 1);
                    $stats[$badGuys]['xgs'] .= number_format($e->xg / 10, 1) . " | ";
                }
            }
        }

        // Do some final time cleanup
        foreach($playingTime as $playerId => $data)
        {
            // End the time range for everyone who was in the game at fulltime
            foreach($playingTime[$playerId]['spans'] as $i => $span)
            {
                if ($span['end'] === null)
                {
                    $playingTime[$playerId]['spans'][$i]['end'] = $fulltime;

                    $start = eventTimeToSeconds($span['start']);
                    $end   = eventTimeToSeconds($fulltime);

                    $secs = $end - $start;

                    $playingTime[$playerId]['seconds'] += $secs;
                }
            }

            // format everyones time in minutes
            $playingTime[$playerId]['minutes'] = secondsToMinutes($playingTime[$playerId]['seconds']);
        }

        return view('games.show', [
            'result'               => $result,
            'resultEvents'         => $resultEvents,
            'goodGuys'             => $goodGuys,
            'badGuys'              => $badGuys,
            'playingTime'          => $playingTime,
            'stats'                => $stats,
            'goals'                => $goals,
            'havePlayingTimeStats' => $havePlayingTimeStats,
            'players'              => $players,
            'starters'             => $starters,
            'managedPlayerIds'     => $managedPlayerIds,
            'chartData'            => $chartData,
            'results'              => $head2HeadResults,
        ]);
    }

    /**
     * preview
     *
     * @return Illuminate\View\View
     */
    public function preview($gameId)
    {
        $result = Result::find($gameId);

        $clubTeamSeason = ClubTeamSeason::find($result->club_team_season_id);

        $currentPlayers = Player::select('players.*', 'rosters.number')
            ->join('rosters', function (JoinClause $join) use ($result) {
                $join->on('rosters.player_id', '=', 'players.id')
                    ->where('club_team_season_id', $result->club_team_season_id);
            })
            ->orderBy('name')
            ->get();

        $guestPlayers = Player::select('players.*', 'roster_guests.number')
            ->join('roster_guests', function (JoinClause $join) use ($result) {
                $join->on('roster_guests.player_id', '=', 'players.id')
                    ->where('result_id', $result->id);
            })
            ->orderBy('name')
            ->get();

        $availablePlayers = PlayerTeam::from('player_teams as pt')
            ->select('p.id', 'p.name')
            ->join('players as p', 'pt.player_id', '=', 'p.id')
            ->where('club_team_id', $clubTeamSeason->club_team_id)
            ->whereNotIn('p.id', function (QueryBuilder $q) use ($result) {
                $q->select('player_id')
                    ->from('rosters')
                    ->where('club_team_season_id', $result->club_team_season_id)
                    ->get();
            })
            ->orderBy('p.name')
            ->get();

        $goodGuys = $result->homeTeam->managed ? 'home' : 'away';
        $badGuys  = $goodGuys == 'home'        ? 'away' : 'home';

        $goodGuysId = $result->{$goodGuys . '_team_id'};
        $badGuysId  = $result->{$badGuys . '_team_id'};

        // Get the last 5 games (all comps)
        $last5Results = Result::where('status', 'D')
            ->where(function (Builder $q) use ($goodGuysId) {
                $q->where('home_team_id', $goodGuysId)
                    ->orWhere('away_team_id', $goodGuysId);
            })
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Get all the head to head games
        $head2HeadResults = Result::where('status', 'D')
            ->where(function (Builder $q) use ($goodGuysId) {
                $q->where('home_team_id', $goodGuysId)
                    ->orWhere('away_team_id', $goodGuysId);
            })
            ->where(function (Builder $q) use ($badGuysId) {
                $q->where('home_team_id', $badGuysId)
                    ->orWhere('away_team_id', $badGuysId);
            })
            ->orderBy('date', 'desc')
            ->get();

        $counts = [
            'W' => 0,
            'D' => 0,
            'L' => 0,
        ];
        $resultIds = [];
        foreach ($head2HeadResults as $r)
        {
            $counts[$r->win_draw_loss]++;
            $resultIds[] = $r->id;
        }

        // Get all the events for all the head 2 head games
        $stats = [
            'good' => [
                'goals'     => 0,
                'shots'     => 0,
                'shots_on'  => 0,
                'shots_off' => 0,
                'corners'   => 0,
                'offsides'  => 0,
                'fouls'     => 0,
            ],
            'bad' => [
                'goals'     => 0,
                'shots'     => 0,
                'shots_on'  => 0,
                'shots_off' => 0,
                'corners'   => 0,
                'offsides'  => 0,
                'fouls'     => 0,
            ],
        ];
        $resultEvents = ResultEvent::whereIn('result_id', $resultIds)
            ->where('event_id', '!=', EnumEvent::start->value)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        foreach ($resultEvents as $e)
        {
            $goodOrBad = $e->against == 1 ? 'bad' : 'good';

            if ($e->event_id == EnumEvent::goal->value)
            {
                $stats[$goodOrBad]['goals']++;
                $stats[$goodOrBad]['shots']++;
                $stats[$goodOrBad]['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_on_target->value)
            {
                $stats[$goodOrBad]['shots']++;
                $stats[$goodOrBad]['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_off_target->value)
            {
                $stats[$goodOrBad]['shots']++;
                $stats[$goodOrBad]['shots_off']++;
            }
            if ($e->event_id == EnumEvent::save->value)
            {
                $stats['bad']['shots']++;
                $stats['bad']['shots_on']++;
            }
            if ($e->event_id == EnumEvent::corner_kick->value)
            {
                $stats[$goodOrBad]['corners']++;
            }
            if ($e->event_id == EnumEvent::foul->value)
            {
                $stats['good']['fouls']++;
            }
            if ($e->event_id == EnumEvent::fouled->value)
            {
                $stats['bad']['fouls']++;
            }
        }

        return view('games.preview', [
            'result'           => $result,
            'goodGuys'         => $goodGuys,
            'badGuys'          => $badGuys,
            'counts'           => $counts,
            'last5Results'     => $last5Results,
            'head2HeadResults' => $head2HeadResults,
            'stats'            => $stats,
            'currentPlayers'   => $currentPlayers,
            'guestPlayers'     => $guestPlayers,
            'availablePlayers' => $availablePlayers,
        ]);
    }

    /**
     * edit
     *
     * @param string  $gameId 
     * @return Illuminate\View\View
     */
    public function edit($gameId)
    {
        $result = Result::find($gameId);

        $goodGuys = $result->homeTeam->managed ? 'home' : 'away';
        $badGuys  = $goodGuys == 'home'        ? 'away' : 'home';

        $clubTeamSeason = ClubTeamSeason::find($result->club_team_season_id);

        // Get all seasons
        $seasons = Season::all()->keyBy('id');

        // Get all active competitions grouped by type
        $competitions = Competition::orderBy('started_at', 'desc')
            ->get()
            ->groupBy('type');

        // Get all locations
        $locations = Location::orderBy('name')
            ->get();

        // Get only managed teams
        $managedTeams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get();

        // Get all non managed teams, group them by club
        $teams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->whereNot('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get()
            ->keyBy('id');

        $teamsByClub = [];
        foreach ($teams as $team)
        {
            $teamsByClub[$team->club_name][] = $team->toArray();
        }

        return view('games.edit', [
            'result'           => $result,
            'goodGuys'         => $goodGuys,
            'badGuys'          => $badGuys,
            'seasons'          => $seasons,
            'competitions'     => $competitions,
            'locations'        => $locations,
            'managedTeams'     => $managedTeams,
            'teamsByClub'      => $teamsByClub,
            'selectedSeasonId' => $clubTeamSeason->season_id,
        ]);
    }

    /**
     * update
     * 
     * @param string  $id 
     * @param Request $request 
     * @return null
     */
    public function update($id, Request $request)
    {
        $validated = $request->validate([
            'season_id'           => 'required|exists:seasons,id',
            'competition_id'      => 'required|exists:competitions,id',
            'location_id'         => 'required|exists:locations,id',
            'date'                => 'required|date_format:Y-m-d',
            'time'                => 'required|date_format:H:i',
            'my_team_id'          => 'required|exists:club_teams,id',
            'my_home_away'        => 'required|in:home,away',
            'my_team_score'       => 'nullable|integer',
            'opponent_team_id'    => 'required|exists:club_teams,id',
            'opponent_team_score' => 'nullable|integer',
            'status'              => [Rule::enum(ResultStatus::class)],
        ]);

        // Get club_team_session_id for this season and team
        $clubTeamSeason = ClubTeamSeason::where('season_id', '=', $request->season_id)
            ->where('club_team_id', '=', $request->my_team_id)
            ->first();

        // Create new result
        $result = Result::find($id);

        $datetime = $request->date . ' ' . $request->time;

        $date = Carbon::createFromFormat('Y-m-d H:i', $datetime, config('stm.timezone_display'));
        $date->tz('UTC');

        $home = 'my';
        $away = 'opponent';

        if ($request->my_home_away == 'away')
        {
            $home = 'opponent';
            $away = 'my';
        }

        $result->club_team_season_id = $clubTeamSeason->id;
        $result->competition_id      = $request->competition_id;
        $result->location_id         = $request->location_id;
        $result->date                = $date;
        $result->home_team_id        = $request->{$home."_team_id"};
        $result->away_team_id        = $request->{$away."_team_id"};
        $result->status              = $request->status;
        $result->created_user_id     = Auth()->user()->id;
        $result->updated_user_id     = Auth()->user()->id;

        if ($request->filled('my_team_score') && $request->filled('opponent_team_score'))
        {
            $result->home_team_score = $request->{$home."_team_score"};
            $result->away_team_score = $request->{$away."_team_score"};
        }
        if ($request->filled('notes'))
        {
            $result->notes = $request->notes;
        }

        $result->save();

        return redirect()->route('games.index');
    }
}
