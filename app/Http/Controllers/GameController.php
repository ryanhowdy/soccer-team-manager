<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\Competition;
use App\Models\Location;
use App\Models\ResultEvent;
use Illuminate\Database\Eloquent\Builder;
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

        $teamsByClub = [];
        foreach ($teams as $team)
        {
            $teamsByClub[$team->club_name][] = $team->toArray();
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
        $recentLocations = Location::orderBy('created_at')
            ->limit(5)
            ->get();

        $locations = collect([
            'Recent' => $recentLocations,
            'All'    => $allLocations,
        ]);

        // Any filters
        $seasonId = $request->has('filter-seasons') ? $request->input('filter-seasons') : $seasons->keys()->last();
        $teamId   = $request->has('filter-teams')   ? $request->input('filter-teams')   : null;

        // Get all the results
        $query = Result::query()->where('status', 'D');

        if (!empty($seasonId))
        {
            $query->where('season_id', $seasonId);
        }

        if (!empty($teamId))
        {
            $query->where(function (Builder $q) use ($teamId) {
                $q->where('home_team_id', $teamId)
                    ->orWhere('away_team_id', $teamId);
            });
        }

        $results = $query->get();

        return view('games', [
            'selectedSeason' => $seasonId,
            'selectedTeam'   => $teamId,
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

        $result = new Result;

        $result->season_id       = $request->season_id;
        $result->competition_id  = $request->competition_id;
        $result->location_id     = $request->location_id;
        $result->date            = Carbon::parse($request->date . ' ' . $request->time);
        $result->status          = ResultStatus::Scheduled;
        $result->created_user_id = Auth()->user()->id;
        $result->updated_user_id = Auth()->user()->id;

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
        $result = Result::find($gameId);

        $resultEvents = ResultEvent::where('result_id', $gameId)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        $goodGuys = $result->homeTeam->managed ? 'home' : 'away';
        $badGuys  = $goodGuys == 'home'        ? 'away' : 'home';

        $stats = [
            'players' => [],
            'home'    => [
                'goals'     => 0,
                'shots'     => 0,
                'shots_on'  => 0,
                'shots_off' => 0,
                'corners'   => 0,
                'offsides'  => 0,
                'fouls'     => 0,
            ],
            'away' => [
                'goals'     => 0,
                'shots'     => 0,
                'shots_on'  => 0,
                'shots_off' => 0,
                'corners'   => 0,
                'offsides'  => 0,
                'fouls'     => 0,
            ],
        ];

        $playingTime = [];
        $fulltime    = 0;

        $playerStatsWeTrack = [
            EnumEvent::goal->value,
            EnumEvent::shot_on_target->value,
            EnumEvent::shot_off_target->value,
            EnumEvent::tackle_won->value,
            EnumEvent::tackle_lost->value,
            EnumEvent::penalty_goal->value,
            EnumEvent::penalty_on_target->value,
            EnumEvent::penalty_off_target->value,
            EnumEvent::free_kick_goal->value,
            EnumEvent::free_kick_on_target->value,
            EnumEvent::free_kick_off_target->value,
        ];

        foreach($resultEvents as $e)
        {
            if (in_array($e->event_id, $playerStatsWeTrack))
            {
                if (!isset($stats['players'][$e->player_id]))
                {
                    $stats['players'][$e->player_id] = [
                        'player'   => $e->player,
                        'goals'    => 0,
                        'assists'  => 0,
                        'shots'    => 0,
                        'shots_on' => 0,
                        'tackles'  => 0,
                    ];
                }
                if ($e->additional && !isset($stats['players'][$e->additional]))
                {
                    $stats['players'][$e->additional] = [
                        'player'   => $e->additionalPlayer,
                        'goals'    => 0,
                        'assists'  => 0,
                        'shots'    => 0,
                        'shots_on' => 0,
                        'tackles'  => 0,
                    ];
                }
            }
            if ($e->event_id == EnumEvent::start->value)
            {
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
            if ($e->event_id == EnumEvent::goal->value)
            {
                $stats[$goodGuys]['goals']++;
                $stats[$goodGuys]['shots']++;
                $stats[$goodGuys]['shots_on']++;

                $stats['players'][$e->player_id]['goals']++;
                $stats['players'][$e->player_id]['shots']++;
                $stats['players'][$e->player_id]['shots_on']++;

                if ($e->additional)
                {
                    $stats['players'][$e->additional]['assists']++;
                }
            }
            if ($e->event_id == EnumEvent::shot_on_target->value)
            {
                $stats[$goodGuys]['shots']++;
                $stats[$goodGuys]['shots_on']++;

                $stats['players'][$e->player_id]['shots']++;
                $stats['players'][$e->player_id]['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_off_target->value)
            {
                $stats[$goodGuys]['shots']++;
                $stats[$goodGuys]['shots_off']++;

                $stats['players'][$e->player_id]['shots']++;
            }
            if ($e->event_id == EnumEvent::goal_against->value)
            {
                $stats[$badGuys]['goals']++;
                $stats[$badGuys]['shots']++;
            }
            if ($e->event_id == EnumEvent::save->value)
            {
                $stats[$badGuys]['shots']++;
                $stats[$badGuys]['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_against->value)
            {
                $stats[$badGuys]['shots']++;
                $stats[$badGuys]['shots_off']++;
            }
            if ($e->event_id == EnumEvent::corner_kick->value)
            {
                $stats[$goodGuys]['corners']++;
            }
            if ($e->event_id == EnumEvent::corner_kick_against->value)
            {
                $stats[$badGuys]['corners']++;
            }
            if ($e->event_id == EnumEvent::fouled->value)
            {
                $stats[$goodGuys]['fouls']++;
            }
            if ($e->event_id == EnumEvent::foul->value)
            {
                $stats[$badGuys]['fouls']++;
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
            'result'      => $result,
            'playingTime' => $playingTime,
            'stats'       => $stats,
        ]);
    }

    /**
     * preview
     *
     * @return Illuminate\View\View
     */
    public function preview(Request $request, $gameId)
    {
        $result = Result::find($gameId);

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
            if ($e->event_id == EnumEvent::goal->value)
            {
                $stats['good']['goals']++;
                $stats['good']['shots']++;
                $stats['good']['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_on_target->value)
            {
                $stats['good']['shots']++;
                $stats['good']['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_off_target->value)
            {
                $stats['good']['shots']++;
                $stats['good']['shots_off']++;
            }
            if ($e->event_id == EnumEvent::goal_against->value)
            {
                $stats['bad']['goals']++;
                $stats['bad']['shots']++;
            }
            if ($e->event_id == EnumEvent::save->value)
            {
                $stats['bad']['shots']++;
                $stats['bad']['shots_on']++;
            }
            if ($e->event_id == EnumEvent::shot_against->value)
            {
                $stats['bad']['shots']++;
                $stats['bad']['shots_off']++;
            }
            if ($e->event_id == EnumEvent::corner_kick->value)
            {
                $stats['good']['corners']++;
            }
            if ($e->event_id == EnumEvent::corner_kick_against->value)
            {
                $stats['bad']['corners']++;
            }
            if ($e->event_id == EnumEvent::fouled->value)
            {
                $stats['good']['fouls']++;
            }
            if ($e->event_id == EnumEvent::foul->value)
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
        ]);
    }
}
