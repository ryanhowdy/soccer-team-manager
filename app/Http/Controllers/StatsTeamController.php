<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\ClubTeamSeason;
use App\Models\ResultEvent;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Event;

class StatsTeamController extends Controller
{
    /**
     * index 
     * 
     * @param Request $request 
     * @return null
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
            ->get()
            ->keyBy('id');

        // Get all seasons
        $seasons = Season::all()->keyBy('id');

        // Any filters
        $managedTeamId = $request->has('filter-managed') ? $request->input('filter-managed') : $managedTeams->keys()->first();
        $seasonId      = $request->has('filter-seasons') ? $request->input('filter-seasons') : $seasons->keys()->last();

        // Turn the season_id into a club_team_season_id
        $clubTeamSeasonIds = ClubTeamSeason::where('season_id', $seasonId)
            ->get()
            ->pluck('id')
            ->toArray();

        // Get all the results for the currently selected filters
        $results = Result::where('status', 'D')
            ->where(function (Builder $q) use ($managedTeamId) {
                $q->where('home_team_id', $managedTeamId)
                    ->orWhere('away_team_id', $managedTeamId);
            })
            ->whereIn('club_team_season_id', $clubTeamSeasonIds)
            ->get();

        $defaults = [
            'wins'            => 0,
            'draws'           => 0,
            'losses'          => 0,
            'games'           => 0,
            'win_percent'     => '',
            'goals'           => 0,
            'goals_against'   => 0,
            'xg'              => 0,
            'xg_against'      => 0,
            'clean'           => 0,
            'shots'           => 0,
            'shot_conversion' => 0,
            'gpg'             => 0,
            'gapg'            => 0,
        ];

        $stats = [
            'homeaway' => [
                'overall' => $defaults,
                'home'    => $defaults,
                'away'    => $defaults,
            ],
            'players' => [],
        ];

        $resultToGoodGuyLkup = [];

        // Get team stats
        foreach ($results as $result)
        {
            $resultIds[$result->id] = $result->id;

            $goodGuys = $result->home_team_id == $managedTeamId ? 'home' : 'away';
            $badGuys  = $goodGuys === 'home'                    ? 'away' : 'home';

            $resultToGoodGuyLkup[$result->id] = $goodGuys;

            $stats['homeaway']['overall']['games']++;
            $stats['homeaway'][$goodGuys]['games']++;
            $stats['homeaway']['overall']['goals']         += $result->{$goodGuys . '_team_score'};
            $stats['homeaway']['overall']['goals_against'] += $result->{$badGuys . '_team_score'};
            $stats['homeaway'][$goodGuys]['goals']         += $result->{$goodGuys . '_team_score'};
            $stats['homeaway'][$goodGuys]['goals_against'] += $result->{$badGuys . '_team_score'};

            // win
            if ($result->{$goodGuys . '_team_score'} > $result->{$badGuys . '_team_score'})
            {
                $stats['homeaway']['overall']['wins']++;
                $stats['homeaway'][$goodGuys]['wins']++;
            }
            // loss
            else if ($result->{$goodGuys . '_team_score'} < $result->{$badGuys . '_team_score'})
            {
                $stats['homeaway']['overall']['losses']++;
                $stats['homeaway'][$goodGuys]['losses']++;
            }
            // draw
            else
            {
                $stats['homeaway']['overall']['draws']++;
                $stats['homeaway'][$goodGuys]['draws']++;
            }

            if ($result->{$badGuys . '_team_score'} == 0)
            {
                $stats['homeaway']['overall']['clean']++;
                $stats['homeaway'][$goodGuys]['clean']++;
            }

            // Do some calculations
            if ($stats['homeaway']['overall']['games'])
            {
                $stats['homeaway']['overall']['win_percent'] = round(($stats['homeaway']['overall']['wins'] / $stats['homeaway']['overall']['games']) * 100);

                $stats['homeaway']['overall']['gpg']  = round($stats['homeaway']['overall']['goals'] / $stats['homeaway']['overall']['games'], 2);
                $stats['homeaway']['overall']['gapg'] = round($stats['homeaway']['overall']['goals_against'] / $stats['homeaway']['overall']['games'], 2);
            }
            if ($stats['homeaway'][$goodGuys]['games'])
            {
                $stats['homeaway'][$goodGuys]['win_percent'] = round(($stats['homeaway'][$goodGuys]['wins'] / $stats['homeaway'][$goodGuys]['games']) * 100);

                $stats['homeaway'][$goodGuys]['gpg']  = round($stats['homeaway'][$goodGuys]['goals'] / $stats['homeaway'][$goodGuys]['games'], 2);
                $stats['homeaway'][$goodGuys]['gapg'] = round($stats['homeaway'][$goodGuys]['goals_against'] / $stats['homeaway'][$goodGuys]['games'], 2);
            }
        }

        $events = ResultEvent::whereIn('result_id', $resultIds)
            ->get();

        $goalEvents    = Event::getGoalValues();
        $shotOnEvents  = Event::getShotOnTargetValues();
        $shotOffEvents = Event::getShotOffTargetValues();
        $fkEvents      = Event::getFreeKickValues();
        $pkEvents      = Event::getPenaltyValues();
        $allShotEvents = array_merge($goalEvents, $shotOnEvents, $shotOffEvents);

        $playerDefaults = [
            'player'   => null,
            'starts'   => 0,
            'mins'     => 0,
            'goals'    => 0,
            'assists'  => 0,
            'shots'    => 0,
            'shotsOn'  => 0,
            'chances'  => 0,
            'fks'      => 0,
            'pks'      => 0,
            'offsides' => 0,
            'tackles'  => 0,
            'yCards'   => 0,
            'rCards'   => 0,
            'time'     => [
                'possibleSecs' => 0,
                'possibleMins' => 0,
                'secs'         => 0,
                'mins'         => 0,
                'spans'        => [],
            ],
        ];

        $fulltime = [];

        // Get player stats
        foreach ($events as $event)
        {
            $homeAway = $resultToGoodGuyLkup[$event->result_id];

            // Bad Guy Events
            if ($event->against)
            {
                if ($event->xg && in_array($event->event_id, $allShotEvents))
                {
                    $stats['homeaway']['overall']['xg_against'] += number_format($event->xg / 10, 1);
                    $stats['homeaway'][$homeAway]['xg_against']  += number_format($event->xg / 10, 1);
                }
            }
            // Good Guy Events
            else
            {
                if ($event->xg && in_array($event->event_id, $allShotEvents))
                {
                    $stats['homeaway']['overall']['xg'] += number_format($event->xg / 10, 1);
                    $stats['homeaway'][$homeAway]['xg'] += number_format($event->xg / 10, 1);

                    $stats['homeaway']['overall']['shots']++;
                    $stats['homeaway'][$homeAway]['shots']++;

                    $stats['homeaway']['overall']['shot_conversion'] = round(($stats['homeaway']['overall']['goals'] / $stats['homeaway']['overall']['shots']) * 100);
                    $stats['homeaway'][$homeAway]['shot_conversion'] = round(($stats['homeaway'][$homeAway]['goals'] / $stats['homeaway'][$homeAway]['shots']) * 100);
                }

                if (!isset($stats['players'][$event->player_name]))
                {
                    $stats['players'][$event->player_name] = $playerDefaults;
                    $stats['players'][$event->player_name]['player'] = $event->player;
                }

                // Start
                if ($event->event_id == Event::start->value)
                {
                    $stats['players'][$event->player_name]['time']['spans'][] = [
                        'game'  => $event->result_id,
                        'start' => '00:00:00',
                        'end'   => null,
                    ];

                    $stats['players'][$event->player_name]['starts']++;
                }
                // Full Time
                if ($event->event_id == Event::fulltime->value)
                {
                    $secs = eventTimeToSeconds($event->time);

                    $fulltime[$event->result_id] = $event->time;

                    $stats['players'][$event->player_name]['time']['possibleSecs'] = $secs;
                }
                // Sub In
                if ($event->event_id == Event::sub_in->value)
                {
                    $stats['players'][$event->player_name]['time']['spans'][] = [
                        'game'  => $event->result_id,
                        'start' => $event->time,
                        'end'   => null,
                    ];
                }

                // Sub Out
                if ($event->event_id == Event::sub_out->value)
                {
                    foreach ($stats['players'][$event->player_name]['time']['spans'] as $i => $span)
                    {
                        if ($span['end'] === null && $span['game'] == $event->result_id)
                        {
                            $stats['players'][$event->player_name]['time']['spans'][$i]['end'] = $event->time;

                            $start = eventTimeToSeconds($span['start']);
                            $end   = eventTimeToSeconds($event->time);

                            $secs = $end - $start;

                            $stats['players'][$event->player_name]['time']['secs'] += $secs;
                        }
                    }
                }
                // Goals/assists
                if (in_array($event->event_id, $goalEvents))
                {
                    if (!empty($event->additional))
                    {
                        if (!isset($stats['players'][$event->additionalPlayer->name]))
                        {
                            $stats['players'][$event->additionalPlayer->name] = $playerDefaults;
                            $stats['players'][$event->additionalPlayer->name]['player'] = $event->additionalPlayer;
                        }

                        $stats['players'][$event->additionalPlayer->name]['assists']++;
                    }

                    $stats['players'][$event->player_name]['goals']++;
                    $stats['players'][$event->player_name]['shots']++;
                    $stats['players'][$event->player_name]['shotsOn']++;
                }
                // Shot on target
                if (in_array($event->event_id, $shotOnEvents))
                {
                    $stats['players'][$event->player_name]['shots']++;
                    $stats['players'][$event->player_name]['shotsOn']++;

                    if (!empty($event->additional))
                    {
                        if (!isset($stats['players'][$event->additionalPlayer->name]))
                        {
                            $stats['players'][$event->additionalPlayer->name] = $playerDefaults;
                            $stats['players'][$event->additionalPlayer->name]['player'] = $event->additionalPlayer;
                        }

                        $stats['players'][$event->additionalPlayer->name]['chances']++;
                    }
                }
                // Shot off target
                if ($event->event_id == Event::shot_off_target->value)
                {
                    $stats['players'][$event->player_name]['shots']++;

                    if (!empty($event->additional))
                    {
                        if (!isset($stats['players'][$event->additionalPlayer->name]))
                        {
                            $stats['players'][$event->additionalPlayer->name] = $playerDefaults;
                            $stats['players'][$event->additionalPlayer->name]['player'] = $event->additionalPlayer;
                        }

                        $stats['players'][$event->additionalPlayer->name]['chances']++;
                    }
                }
                // Free kicks
                if (in_array($event->event_id, $fkEvents))
                {
                    $stats['players'][$event->player_name]['fks']++;
                }
                // Penalties
                if (in_array($event->event_id, $pkEvents))
                {
                    $stats['players'][$event->player_name]['pks']++;
                }
                // Offsides
                if ($event->event_id == Event::offsides->value)
                {
                    $stats['players'][$event->player_name]['offsides']++;
                }
                // Tackles
                if ($event->event_id == Event::tackle_won->value)
                {
                    $stats['players'][$event->player_name]['tackles']++;
                }
                // Yellow Cards
                if ($event->event_id == Event::yellow_card->value)
                {
                    $stats['players'][$event->player_name]['yCards']++;
                }
                // Red Cards
                if ($event->event_id == Event::red_card->value)
                {
                    $stats['players'][$event->player_name]['rCards']++;
                }
            }
        }

        // Do some final cleanup/calculations
        foreach($stats['players'] as $name => $player)
        {
            $stats['players'][$name]['percent'] = [
                'goals'          => round(($player['goals'] / $stats['homeaway']['overall']['goals']) * 100),
                'assists'        => 0,
                'shotConversion' => $player['goals'] ? round(($player['goals'] / $player['shots']) * 100) : 0,
            ];

            if ($fulltime)
            {
                foreach ($stats['players'][$name]['time']['spans'] as $i => $span)
                {
                    if ($span['end'] === null)
                    {
                        $stats['players'][$name]['time']['spans'][$i]['end'] = $fulltime[ $span['game'] ];

                        $start = eventTimeToSeconds($span['start']);
                        $end   = eventTimeToSeconds($fulltime[ $span['game'] ]);

                        $secs = $end - $start;

                        $stats['players'][$name]['time']['secs'] += $secs;
                    }
                }
            }

            // format everyones time in minutes
            $stats['players'][$name]['time']['minutes']      = secondsToMinutes($stats['players'][$name]['time']['secs']);
            $stats['players'][$name]['time']['possibleMins'] = secondsToMinutes($stats['players'][$name]['time']['possibleSecs']);
        }

        return view('stats.team', [
            'selectedManagedTeamId' => $managedTeamId,
            'selectedSeason'        => $seasonId,
            'managedTeams'          => $managedTeams,
            'seasons'               => $seasons,
            'results'               => $results,
            'stats'                 => $stats,
        ]);
    }
}
