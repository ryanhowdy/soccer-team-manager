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
        // Get all seasons, newest first
        $seasons = Season::newestFirst()->get()->keyBy('id');

        // Any filters
        $selectedSeason = resolveSeasonFilter($request, $seasons);

        // Turn the season_id into a club_team_season_id
        $clubTeamSeasonIds = $selectedSeason
            ? ClubTeamSeason::where('season_id', $selectedSeason->id)->pluck('id')->toArray()
            : null;

        // Get all the results for the currently selected filters
        $results = Result::where('status', 'D')
            ->where(function (Builder $q) {
                $q->where('home_team_id', auth()->user()->selected_club_team_id)
                    ->orWhere('away_team_id', auth()->user()->selected_club_team_id);
            })
            ->when($clubTeamSeasonIds !== null, fn ($q) => $q->whereIn('club_team_season_id', $clubTeamSeasonIds))
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

        $resultIds = [];

        // Get team stats
        foreach ($results as $result)
        {
            $resultIds[$result->id] = $result->id;

            $goodGuys = $result->home_team_id == auth()->user()->selected_club_team_id ? 'home' : 'away';
            $badGuys  = $goodGuys === 'home' ? 'away' : 'home';

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

        $events = [];
        if ($resultIds)
        {
            $events = ResultEvent::whereIn('result_id', $resultIds)
                ->get();
        }

        $goalEvents    = Event::getGoalValues();
        $assistEvents  = Event::getAssistValues();
        $chanceEvents  = Event::getChanceValues();
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
                // Goals
                if (in_array($event->event_id, $goalEvents))
                {
                    $stats['players'][$event->player_name]['goals']++;
                    $stats['players'][$event->player_name]['shots']++;
                    $stats['players'][$event->player_name]['shotsOn']++;
                }
                // Assists.  Not every goal can be assisted - see
                // Event::getAssistValues().
                if (in_array($event->event_id, $assistEvents) && !empty($event->additional))
                {
                    if (!isset($stats['players'][$event->additionalPlayer->name]))
                    {
                        $stats['players'][$event->additionalPlayer->name] = $playerDefaults;
                        $stats['players'][$event->additionalPlayer->name]['player'] = $event->additionalPlayer;
                    }

                    $stats['players'][$event->additionalPlayer->name]['assists']++;
                }
                // Shot on target
                if (in_array($event->event_id, $shotOnEvents))
                {
                    $stats['players'][$event->player_name]['shots']++;
                    $stats['players'][$event->player_name]['shotsOn']++;
                }
                // Shot off target
                if ($event->event_id == Event::shot_off_target->value)
                {
                    $stats['players'][$event->player_name]['shots']++;
                }
                // Chance creation - the pass that set up a goal or shot.
                // Which events qualify is Event::getChanceValues(), so this
                // and the home dashboard cannot drift apart.
                if (in_array($event->event_id, $chanceEvents) && !empty($event->additional))
                {
                    if (!isset($stats['players'][$event->additionalPlayer->name]))
                    {
                        $stats['players'][$event->additionalPlayer->name] = $playerDefaults;
                        $stats['players'][$event->additionalPlayer->name]['player'] = $event->additionalPlayer;
                    }

                    $stats['players'][$event->additionalPlayer->name]['chances']++;
                }
                // Free kicks, credited to whoever took them.  On an indirect
                // free kick the event sits on the player who finished it and
                // `additional` holds the taker, so the taker wins when there is
                // one - heading in a free kick is not the same as taking one.
                //
                // Only worked out inside this branch: `additional` holds a
                // position string on start/sub_in events, not a player.
                if (in_array($event->event_id, $fkEvents))
                {
                    $taker     = $event->additionalPlayer ?: $event->player;
                    $takerName = $taker ? $taker->name : $event->player_name;

                    if (!isset($stats['players'][$takerName]))
                    {
                        $stats['players'][$takerName] = $playerDefaults;
                        $stats['players'][$takerName]['player'] = $taker;
                    }

                    $stats['players'][$takerName]['fks']++;
                }
                // Penalties always belong to the player who struck them.  A
                // penalty cannot be indirect, so `additional` is never a taker
                // here whatever else it may have been recorded for.
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
            $pGoals = 0;
            if ($stats['homeaway']['overall']['goals'])
            {
                $pGoals = round(($player['goals'] / $stats['homeaway']['overall']['goals']) * 100);
            }

            $stats['players'][$name]['percent'] = [
                'goals'          => $pGoals,
                'assists'        => 0,
                'shotConversion' => $player['goals'] ? round(($player['goals'] / $player['shots']) * 100) : 0,
            ];

            // Close any unclosed spans at each game's recorded fulltime. Older
            // games were tracked without a fulltime event, so there's nothing to
            // close the span against — leave it open and count no time for it.
            foreach ($stats['players'][$name]['time']['spans'] as $i => $span)
            {
                if ($span['end'] !== null || !isset($fulltime[ $span['game'] ]))
                {
                    continue;
                }

                $ft = $fulltime[ $span['game'] ];

                $stats['players'][$name]['time']['spans'][$i]['end'] = $ft;

                $start = eventTimeToSeconds($span['start']);
                $end   = eventTimeToSeconds($ft);

                $secs = $end - $start;

                $stats['players'][$name]['time']['secs'] += $secs;
            }

            // format everyones time in minutes
            $stats['players'][$name]['time']['minutes']      = secondsToMinutes($stats['players'][$name]['time']['secs']);
            $stats['players'][$name]['time']['possibleMins'] = secondsToMinutes($stats['players'][$name]['time']['possibleSecs']);
        }

        return view('stats.team', [
            'selectedSeason' => $selectedSeason,
            'seasons'        => $seasons,
            'results'        => $results,
            'stats'          => $stats,
        ]);
    }
}
