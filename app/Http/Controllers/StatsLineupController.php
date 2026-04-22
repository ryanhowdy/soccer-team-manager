<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\ClubTeamSeason;
use App\Models\ManagedPlayer;
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
        // Get all seasons
        $seasons = Season::all()->keyBy('id');

        // Any filters
        $seasonId = $request->has('filter-seasons') ? $request->input('filter-seasons') : $seasons->keys()->last();

        // Turn the season_id into a club_team_season_id
        $clubTeamSeasonIds = ClubTeamSeason::where('season_id', $seasonId)
            ->get()
            ->pluck('id')
            ->toArray();

        $clubTeamId = auth()->user()->selected_club_team_id;

        // Get all the results for the currently selected filters
        $results = Result::where('status', 'D')
            ->where(function (Builder $q) use ($clubTeamId) {
                $q->where('home_team_id', $clubTeamId)
                    ->orWhere('away_team_id', $clubTeamId);
            })
            ->whereIn('club_team_season_id', $clubTeamSeasonIds)
            ->get()
            ->keyBy('id');

        $managedPlayerIds = ManagedPlayer::where('user_id', auth()->user()->id)
            ->pluck('player_id')
            ->flip()
            ->toArray();

        $lineups        = [];
        $playerNameLkup = [];
        $fulltimes      = [];

        $events = ResultEvent::whereIn('result_id', $results->keys())
            ->get();

        // Build lineup spans per game/player
        foreach ($events as $event)
        {
            $playerNameLkup[$event->player_id] = $event->player_name;

            if (!isset($lineups[$event->result_id][$event->player_id])) {
                $lineups[$event->result_id][$event->player_id] = [];
            }

            if ($event->event_id == Event::start->value)
            {
                $lineups[$event->result_id][$event->player_id][] = [
                    'start' => '00:00:00',
                    'end'   => null,
                ];
            }
            if ($event->event_id == Event::fulltime->value)
            {
                $fulltimes[$event->result_id] = $event->time;
            }
            if ($event->event_id == Event::sub_in->value)
            {
                $lineups[$event->result_id][$event->player_id][] = [
                    'start' => $event->time,
                    'end'   => null,
                ];
            }
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

        // Close any unclosed spans at each game's recorded fulltime
        foreach ($lineups as $resultId => $byPlayer)
        {
            if (!isset($fulltimes[$resultId])) {
                continue;
            }

            $ft = $fulltimes[$resultId];

            foreach ($byPlayer as $pid => $spans)
            {
                foreach ($spans as $i => $span)
                {
                    if ($span['end'] === null)
                    {
                        $lineups[$resultId][$pid][$i]['end'] = $ft;
                    }
                }
            }
        }

        // Sort function: managed players first, then alphabetically by name
        $sortFn = function ($a, $b) use ($managedPlayerIds, $playerNameLkup) {
            $aManaged = isset($managedPlayerIds[$a]) ? 1 : 0;
            $bManaged = isset($managedPlayerIds[$b]) ? 1 : 0;
            if ($aManaged !== $bManaged) {
                return $bManaged - $aManaged;
            }
            return strcmp($playerNameLkup[$a] ?? '', $playerNameLkup[$b] ?? '');
        };

        // Walk each game's timeline in segments between sub/start/end events
        // and tally minutes for each lineup combo and each player
        $playerSeconds = [];
        $lineupSeconds = [];
        $lineupGames   = [];
        $playerGames   = [];
        $lineupPlayers = [];

        foreach ($results as $resultId => $result)
        {
            if (empty($lineups[$resultId] ?? [])) {
                continue;
            }

            $gameLineups = $lineups[$resultId];

            // Collect all span edges as breakpoint seconds
            $breakpoints = [];
            foreach ($gameLineups as $playerId => $spans)
            {
                foreach ($spans as $span)
                {
                    if ($span['end'] === null) continue;
                    $breakpoints[] = eventTimeToSeconds($span['start']);
                    $breakpoints[] = eventTimeToSeconds($span['end']);
                }
            }
            $breakpoints = array_values(array_unique($breakpoints));
            sort($breakpoints);

            for ($i = 0; $i < count($breakpoints) - 1; $i++)
            {
                $t1 = $breakpoints[$i];
                $t2 = $breakpoints[$i + 1];
                $duration = $t2 - $t1;
                if ($duration <= 0) continue;
                $mid = $t1 + intdiv($duration, 2);

                // Find the lineup on the field at the midpoint of this segment
                $lineup = [];
                foreach ($gameLineups as $playerId => $spans)
                {
                    foreach ($spans as $s)
                    {
                        if ($s['end'] === null) continue;
                        $start = eventTimeToSeconds($s['start']);
                        $end   = eventTimeToSeconds($s['end']);
                        if ($mid >= $start && $mid <= $end)
                        {
                            $lineup[] = $playerId;
                            continue 2;
                        }
                    }
                }
                if (empty($lineup)) continue;

                usort($lineup, $sortFn);
                $lineupKey = implode('-', $lineup);

                $lineupSeconds[$lineupKey] = ($lineupSeconds[$lineupKey] ?? 0) + $duration;
                $lineupGames[$lineupKey][$resultId] = 1;

                foreach ($lineup as $pid)
                {
                    $playerSeconds[$pid] = ($playerSeconds[$pid] ?? 0) + $duration;
                    $playerGames[$pid][$resultId] = 1;
                }

                if (!isset($lineupPlayers[$lineupKey]))
                {
                    $lineupPlayers[$lineupKey] = array_map(fn($id) => [
                        'id'      => $id,
                        'name'    => $playerNameLkup[$id] ?? $id,
                        'managed' => isset($managedPlayerIds[$id]),
                    ], $lineup);
                }
            }
        }

        // W/D/L per result from the selected club team's perspective
        $resultWDL = [];
        foreach ($results as $resultId => $result)
        {
            $isHome     = $result->home_team_id == $clubTeamId;
            $myScore    = $isHome ? $result->home_team_score : $result->away_team_score;
            $theirScore = $isHome ? $result->away_team_score : $result->home_team_score;

            if ($myScore > $theirScore) {
                $resultWDL[$resultId] = 'W';
            } elseif ($myScore < $theirScore) {
                $resultWDL[$resultId] = 'L';
            } else {
                $resultWDL[$resultId] = 'D';
            }
        }

        $stats = [
            'lineups' => [],
            'players' => [],
        ];

        // Seed lineup/player entries from the minute totals
        foreach ($lineupSeconds as $key => $secs)
        {
            $stats['lineups'][$key] = [
                'players'       => $lineupPlayers[$key],
                'seconds'       => $secs,
                'games'         => $lineupGames[$key] ?? [],
                'goals'         => 0,
                'goals_against' => 0,
                'xg'            => 0,
                'xg_against'    => 0,
            ];
        }

        foreach ($playerSeconds as $pid => $secs)
        {
            $stats['players'][$pid] = [
                'name'          => $playerNameLkup[$pid] ?? $pid,
                'managed'       => isset($managedPlayerIds[$pid]),
                'seconds'       => $secs,
                'games'         => $playerGames[$pid] ?? [],
                'goals'         => 0,
                'goals_against' => 0,
                'xg'            => 0,
                'xg_against'    => 0,
                'wins'          => 0,
                'draws'         => 0,
                'losses'        => 0,
            ];
        }

        // Tally W/D/L per player per appearance
        foreach ($stats['players'] as $pid => $data)
        {
            foreach (array_keys($data['games']) as $resultId)
            {
                $wdl = $resultWDL[$resultId] ?? null;
                if ($wdl === 'W') $stats['players'][$pid]['wins']++;
                elseif ($wdl === 'L') $stats['players'][$pid]['losses']++;
                elseif ($wdl === 'D') $stats['players'][$pid]['draws']++;
            }
        }

        // Attribute goals and xG to lineup/players on the field at event time
        $goalEvents    = Event::getGoalValues();
        $shotOnEvents  = Event::getShotOnTargetValues();
        $shotOffEvents = Event::getShotOffTargetValues();
        $allShotEvents = array_merge($goalEvents, $shotOnEvents, $shotOffEvents);

        foreach ($events as $event)
        {
            if (!in_array($event->event_id, $allShotEvents)) continue;
            if (!isset($lineups[$event->result_id])) continue;

            $lineup = getLineupForEventTime($event->time, $lineups[$event->result_id]);
            if (empty($lineup)) continue;

            usort($lineup, $sortFn);
            $lineupKey = implode('-', $lineup);

            $xg     = $event->xg ? $event->xg / 10 : 0;
            $isGoal = in_array($event->event_id, $goalEvents);

            if (isset($stats['lineups'][$lineupKey]))
            {
                if ($event->against) {
                    if ($isGoal) $stats['lineups'][$lineupKey]['goals_against']++;
                    $stats['lineups'][$lineupKey]['xg_against'] += $xg;
                } else {
                    if ($isGoal) $stats['lineups'][$lineupKey]['goals']++;
                    $stats['lineups'][$lineupKey]['xg'] += $xg;
                }
            }

            foreach ($lineup as $pid)
            {
                if (!isset($stats['players'][$pid])) continue;
                if ($event->against) {
                    if ($isGoal) $stats['players'][$pid]['goals_against']++;
                    $stats['players'][$pid]['xg_against'] += $xg;
                } else {
                    if ($isGoal) $stats['players'][$pid]['goals']++;
                    $stats['players'][$pid]['xg'] += $xg;
                }
            }
        }

        // Derived per-90 rates and win %
        foreach ($stats['lineups'] as $key => $s)
        {
            $mins  = $s['seconds'] / 60;
            $p90   = $mins > 0 ? 90 / $mins : 0;

            $stats['lineups'][$key]['minutes']      = round($mins, 1);
            $stats['lineups'][$key]['goals_per_90'] = round($s['goals'] * $p90, 2);
            $stats['lineups'][$key]['ga_per_90']    = round($s['goals_against'] * $p90, 2);
            $stats['lineups'][$key]['xg_per_90']    = round($s['xg'] * $p90, 2);
            $stats['lineups'][$key]['xga_per_90']   = round($s['xg_against'] * $p90, 2);
            $stats['lineups'][$key]['diff_per_90']  = round(($s['goals'] - $s['goals_against']) * $p90, 2);
        }

        foreach ($stats['players'] as $pid => $s)
        {
            $mins  = $s['seconds'] / 60;
            $p90   = $mins > 0 ? 90 / $mins : 0;
            $games = count($s['games']);

            $stats['players'][$pid]['minutes']      = round($mins, 1);
            $stats['players'][$pid]['goals_per_90'] = round($s['goals'] * $p90, 2);
            $stats['players'][$pid]['ga_per_90']    = round($s['goals_against'] * $p90, 2);
            $stats['players'][$pid]['xg_per_90']    = round($s['xg'] * $p90, 2);
            $stats['players'][$pid]['xga_per_90']   = round($s['xg_against'] * $p90, 2);
            $stats['players'][$pid]['win_pct']      = $games > 0 ? round(($s['wins'] / $games) * 100) : 0;
        }

        return view('stats.lineup', [
            'selectedSeason' => $seasonId,
            'seasons'        => $seasons,
            'stats'          => $stats,
        ]);
    }
}
