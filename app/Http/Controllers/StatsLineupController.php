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

class StatsLineupController extends Controller
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
            ->get()
            ->keyBy('id');

        $lineups        = [];
        $playerNameLkup = [];

        $stats = [
            'lineups' => [],
            'players' => [],
        ];

        $events = ResultEvent::whereIn('result_id', $results->keys())
            ->get();

        // Get the lineup start/stops
        foreach ($events as $event)
        {
            $playerNameLkup[$event->player_id] = $event->player_name;

            if (!isset($lineups[$event->result_id][$event->player_id])) {
                $lineups[$event->result_id][$event->player_id] = [];
            }

            // Start
            if ($event->event_id == Event::start->value)
            {
                $lineups[$event->result_id][$event->player_id][] = [
                    'start' => '00:00:00',
                    'end'   => null,
                ];
            }
            // Full Time
            if ($event->event_id == Event::fulltime->value)
            {
                $last = array_key_last($lineups[$event->result_id][$event->player_id]);

                if (!is_null($last))
                {
                    $lineups[$event->result_id][$event->player_id][$last]['end'] = $event->time;
                }

            }
            // Sub In
            if ($event->event_id == Event::sub_in->value)
            {
                $lineups[$event->result_id][$event->player_id][] = [
                    'start' => $event->time,
                    'end'   => null,
                ];
            }

            // Sub Out
            if ($event->event_id == Event::sub_out->value)
            {
                foreach ($lineups[$event->result_id][$event->player_id] as $i => $span)
                {
                    if ($span['end'] === null)
                    {
                        $lineups[$event->result_id][$event->player_id][$i]['end'] = $event->time;
                    }
                }
            }
        }

        $goalEvents = Event::getGoalValues();

        // Get stats
        foreach ($events as $event)
        {
            if (in_array($event->event_id, $goalEvents))
            {
                $lineup = getLineupForEventTime($event->time, $lineups[$event->result_id]);

                if (empty($lineup))
                {
                    continue;
                }

                // player stats
                foreach ($lineup as $playerId)
                {
                    if (!isset($stats['players'][$playerId])) {
                        $stats['players'][$playerId] = [
                            'name'          => $playerNameLkup[$playerId],
                            'games'         => [],
                            'goals'         => 0,
                            'goals_against' => 0,
                        ];
                    }

                    $stats['players'][$playerId]['games'][$event->result_id] = 1;

                    if ($event->against)
                    {
                        $stats['players'][$playerId]['goals_against']++;
                    }
                    else
                    {
                        $stats['players'][$playerId]['goals']++;
                    }
                }

                $lineup = implode('-', $lineup);

                // lineup stats
                if (!isset($stats['lineups'][$lineup])) {
                    $stats['lineups'][$lineup] = [
                        'games'         => [],
                        'goals'         => 0,
                        'goals_against' => 0,
                    ];
                }

                $stats['lineups'][$lineup]['games'][$event->result_id] = 1;

                if ($event->against)
                {
                    $stats['lineups'][$lineup]['goals_against']++;
                }
                else
                {
                    $stats['lineups'][$lineup]['goals']++;
                }
            }
        }

        return view('stats.lineup', [
            'selectedManagedTeamId' => $managedTeamId,
            'selectedSeason'        => $seasonId,
            'managedTeams'          => $managedTeams,
            'seasons'               => $seasons,
            'stats'                 => $stats,
        ]);
    }
}
