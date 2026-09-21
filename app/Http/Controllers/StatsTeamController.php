<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\ClubTeamSeason;
use App\Models\PlayerGameRating;
use App\Models\ResultEvent;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Event;

class StatsTeamController extends Controller
{
    /**
     * A player needs at least this many full games' worth of minutes before
     * their per-full-game rates mean anything.  Without it a substitute who
     * scored once in ten minutes tops the table at six goals a game.
     */
    private const RATE_MIN_FULL_GAMES = 1;

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
            'keepers' => [],
            // The divisor the Per Full Game columns use, worked out from the
            // games in this filter - see below.
            'fullGameMins' => 0,
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

        $events = collect();
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
            'xg'       => 0,
            'chances'  => 0,
            'fks'      => 0,
            'pks'      => 0,
            'offsides' => 0,
            'tackles'  => 0,
            'yCards'   => 0,
            'rCards'   => 0,
            'rating'   => null,
            'rated'    => 0,
            'time'     => [
                'secs'  => 0,
                'spans' => [],
            ],
        ];

        $fulltime = [];

        // Team totals the Percentages columns divide by.  A player's share of
        // the assists is measured against the assists the team actually
        // recorded, not against its goals - only some goals are assisted.
        $teamAssists = 0;
        $teamChances = 0;

        // Get player stats
        foreach ($events as $event)
        {
            $homeAway = $resultToGoodGuyLkup[$event->result_id];

            // Bad Guy Events
            if ($event->against)
            {
                if ($event->xg !== null && in_array($event->event_id, $allShotEvents))
                {
                    $stats['homeaway']['overall']['xg_against'] += $event->xg / 10;
                    $stats['homeaway'][$homeAway]['xg_against'] += $event->xg / 10;
                }
            }
            // Good Guy Events
            else
            {
                // A shot the opposition put on target and our keeper stopped.
                // It is recorded on our keeper rather than against us, so the
                // branch above never sees it - without this, every saved shot
                // would be missing from xG Against.
                if ($event->event_id == Event::save->value && $event->xg !== null)
                {
                    $stats['homeaway']['overall']['xg_against'] += $event->xg / 10;
                    $stats['homeaway'][$homeAway]['xg_against'] += $event->xg / 10;
                }

                // A shot counts as a shot whether or not anyone got round to
                // rating it, so the tally sits outside the xG check.  Note
                // that an xG of 0 is a legitimate rating and a falsy value,
                // hence the !== null rather than a plain truth test.
                if (in_array($event->event_id, $allShotEvents))
                {
                    if ($event->xg !== null)
                    {
                        $stats['homeaway']['overall']['xg'] += $event->xg / 10;
                        $stats['homeaway'][$homeAway]['xg'] += $event->xg / 10;
                    }

                    $stats['homeaway']['overall']['shots']++;
                    $stats['homeaway'][$homeAway]['shots']++;

                    $stats['homeaway']['overall']['shot_conversion'] = round(($stats['homeaway']['overall']['goals'] / $stats['homeaway']['overall']['shots']) * 100);
                    $stats['homeaway'][$homeAway]['shot_conversion'] = round(($stats['homeaway'][$homeAway]['goals'] / $stats['homeaway'][$homeAway]['shots']) * 100);
                }

                // Full Time belongs to the game's clock, not to anybody on the
                // field, so it is dealt with before the per-player work below.
                if ($event->event_id == Event::fulltime->value)
                {
                    $fulltime[$event->result_id] = $event->time;
                }

                // Everything past here is credited to a player.  An event
                // without one - fulltime, a possession swing - would otherwise
                // be filed under a player called 'Unknown'.
                if (!$event->player_id)
                {
                    continue;
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
                // Shot quality, credited to whoever struck the shot.  Every
                // shot event carries its own xG whether it went in or not, so
                // this sits outside the goal/on target/off target branches
                // below rather than being repeated in each of them.
                if (in_array($event->event_id, $allShotEvents) && $event->xg !== null)
                {
                    $stats['players'][$event->player_name]['xg'] += $event->xg / 10;
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

                    $teamAssists++;
                }
                // Shot on target
                if (in_array($event->event_id, $shotOnEvents))
                {
                    $stats['players'][$event->player_name]['shots']++;
                    $stats['players'][$event->player_name]['shotsOn']++;
                }
                // Shot off target.  All three off target events count, to match
                // the on target side above - a free kick dragged wide is still
                // a shot the player took.
                if (in_array($event->event_id, $shotOffEvents))
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

                    $teamChances++;
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

        // xG is summed a tenth at a time, so round it once at the end rather
        // than letting the accumulated float reach the page.
        foreach (['overall', 'home', 'away'] as $side)
        {
            $stats['homeaway'][$side]['xg']         = round($stats['homeaway'][$side]['xg'], 1);
            $stats['homeaway'][$side]['xg_against'] = round($stats['homeaway'][$side]['xg_against'], 1);
        }

        // What a full game is worth in this filter.  Youth games are not 90
        // minutes and not all the same length either, so the per-game rates
        // divide by how long these games actually ran.  With no fulltime
        // recorded - nothing before the 2024 Fall season has it - there is no
        // honest divisor and the rate columns stay hidden.
        if ($fulltime)
        {
            $totalSecs = 0;

            foreach ($fulltime as $ft)
            {
                $totalSecs += eventTimeToSeconds($ft);
            }

            $stats['fullGameMins'] = round(($totalSecs / count($fulltime)) / 60);
        }

        $stats['keepers'] = $this->keeperStats($events, $goalEvents);

        $stats['ratings'] = $this->playerRatings($resultIds);

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
                'assists'        => $teamAssists ? round(($player['assists'] / $teamAssists) * 100) : 0,
                'chances'        => $teamChances ? round(($player['chances'] / $teamChances) * 100) : 0,
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
            $stats['players'][$name]['time']['minutes'] = secondsToMinutes($stats['players'][$name]['time']['secs']);

            $stats['players'][$name]['xg'] = round($player['xg'], 1);

            // What the player is worth over a full game, rather than over the
            // 90 minutes nobody here plays.  Anyone short of a full game's
            // minutes gets no rate at all - see RATE_MIN_FULL_GAMES.
            $stats['players'][$name]['per'] = $this->perFullGame(
                $stats['players'][$name],
                $stats['fullGameMins']
            );

            if (isset($stats['ratings'][$name]))
            {
                $stats['players'][$name]['rating'] = $stats['ratings'][$name]['rating'];
                $stats['players'][$name]['rated']  = $stats['ratings'][$name]['games'];
            }
        }

        return view('stats.team', [
            'selectedSeason' => $selectedSeason,
            'seasons'        => $seasons,
            'results'        => $results,
            'stats'          => $stats,
        ]);
    }

    /**
     * A player's output over one full game.
     *
     * Returns null for every rate when the filter has no game length to divide
     * by, or when the player has not been on the field long enough for a rate
     * to say anything.
     *
     * @param  array $player
     * @param  int   $fullGameMins
     * @return array
     */
    private function perFullGame(array $player, int $fullGameMins)
    {
        $none = ['goals' => null, 'assists' => null, 'xg' => null];

        if (!$fullGameMins)
        {
            return $none;
        }

        $mins = $player['time']['minutes'];

        if ($mins < ($fullGameMins * self::RATE_MIN_FULL_GAMES))
        {
            return $none;
        }

        $games = $mins / $fullGameMins;

        return [
            'goals'   => round($player['goals'] / $games, 2),
            'assists' => round($player['assists'] / $games, 2),
            'xg'      => round($player['xg'] / $games, 2),
        ];
    }

    /**
     * Season ratings, keyed by player name.
     *
     * Several people can rate the same player in the same game, so a game's
     * rating is the average of those and the season rating is the average of
     * the player's rated games.  Averaging the raw rows instead would weight a
     * game by how many people bothered to rate it.
     *
     * @param  array $resultIds
     * @return array
     */
    private function playerRatings(array $resultIds)
    {
        $ratings = [];

        if (!$resultIds)
        {
            return $ratings;
        }

        $rows = PlayerGameRating::with('player')
            ->whereIn('result_id', $resultIds)
            ->get();

        foreach ($rows->groupBy('player_id') as $playerRows)
        {
            $gameRatings = $playerRows->groupBy('result_id')
                ->map(fn ($rows) => $rows->avg('rating'));

            $player = $playerRows->first()->player;

            $ratings[$player ? $player->name : 'Unknown'] = [
                'rating' => round($gameRatings->avg(), 1),
                'games'  => $gameRatings->count(),
            ];
        }

        return $ratings;
    }

    /**
     * Goalkeeping, keyed by player name.
     *
     * Saves are recorded on the keeper who made them, so those are never in
     * doubt.  Goals conceded are not recorded on anybody, so they have to be
     * charged to whoever was in goal at the time.  That comes from the
     * position in `additional` on a start or sub_in, which only became
     * reliable in the 2024 Fall season - so a game where nobody is marked in
     * goal contributes saves and nothing else, and the keeper's save
     * percentage and goals prevented say how many games they are built from.
     *
     * @param  \Illuminate\Support\Collection $events
     * @param  array $goalEvents
     * @return array
     */
    private function keeperStats($events, array $goalEvents)
    {
        $spans   = $this->keeperSpans($events);
        $keepers = [];

        $defaults = [
            'player'  => null,
            'saves'   => 0,
            'games'   => [],
            // Only what happened in a game we can pin on a keeper.
            'known'   => [],
            'shots'   => 0,
            'ga'      => 0,
            'xgFaced' => 0,
        ];

        foreach ($events as $event)
        {
            $known = isset($spans[$event->result_id]);

            // A save, recorded on our keeper rather than against us.
            if (!$event->against && $event->event_id == Event::save->value && $event->player_id)
            {
                $name = $event->player_name;

                $keepers[$name] ??= $defaults;
                $keepers[$name]['player'] = $event->player;
                $keepers[$name]['saves']++;
                $keepers[$name]['games'][$event->result_id] = true;

                if ($known)
                {
                    $keepers[$name]['known'][$event->result_id] = true;
                    $keepers[$name]['shots']++;
                    $keepers[$name]['xgFaced'] += $event->xg !== null ? $event->xg / 10 : 0;
                }
            }

            // A goal conceded, charged to whoever was in goal when it went in.
            if ($event->against && in_array($event->event_id, $goalEvents) && $known)
            {
                $name = $this->keeperAt($spans[$event->result_id], $event->time);

                if (!$name)
                {
                    continue;
                }

                $keepers[$name] ??= $defaults;
                $keepers[$name]['games'][$event->result_id] = true;
                $keepers[$name]['known'][$event->result_id] = true;
                $keepers[$name]['ga']++;
                $keepers[$name]['shots']++;
                $keepers[$name]['xgFaced'] += $event->xg !== null ? $event->xg / 10 : 0;
            }
        }

        foreach ($keepers as $name => $keeper)
        {
            $known = count($keeper['known']);

            $keepers[$name]['games']     = count($keeper['games']);
            $keepers[$name]['known']     = $known;
            $keepers[$name]['xgFaced']   = $known ? round($keeper['xgFaced'], 1) : null;
            $keepers[$name]['ga']        = $known ? $keeper['ga'] : null;
            $keepers[$name]['savePct']   = $known && $keeper['shots']
                ? round((($keeper['shots'] - $keeper['ga']) / $keeper['shots']) * 100)
                : null;
            $keepers[$name]['prevented'] = $known
                ? round($keeper['xgFaced'] - $keeper['ga'], 1)
                : null;

            // Nothing on the player's row needs the raw shot count once the
            // percentage is worked out.
            unset($keepers[$name]['shots']);
        }

        uasort($keepers, fn ($a, $b) => $b['saves'] <=> $a['saves']);

        return $keepers;
    }

    /**
     * Who kept goal, and when, in each game.
     *
     * `additional` on a start or sub_in holds the position, so 'G' is how a
     * keeper announces themselves.  A sub_out closes their span; a span left
     * open runs to the end of the game.  Games where nobody is marked in goal
     * get no entry at all, which is what marks them as unattributable.
     *
     * @param  \Illuminate\Support\Collection $events
     * @return array
     */
    private function keeperSpans($events)
    {
        $spans = [];

        $onField = [Event::start->value, Event::sub_in->value];

        foreach ($events->groupBy('result_id') as $resultId => $resultEvents)
        {
            // The events come back in insertion order, which is close to but
            // not necessarily the order they happened in.
            $ordered = $resultEvents->sort(function ($a, $b) {
                $cmp = eventTimeToSeconds($a->time) <=> eventTimeToSeconds($b->time);

                return $cmp !== 0 ? $cmp : ($a->id <=> $b->id);
            });

            foreach ($ordered as $event)
            {
                if ($event->against || !$event->player_id)
                {
                    continue;
                }

                if (in_array($event->event_id, $onField) && $event->additional === 'G')
                {
                    $spans[$resultId][] = [
                        'player' => $event->player_name,
                        'start'  => eventTimeToSeconds($event->time),
                        'end'    => null,
                    ];
                }

                if ($event->event_id == Event::sub_out->value && isset($spans[$resultId]))
                {
                    foreach ($spans[$resultId] as $i => $span)
                    {
                        if ($span['end'] === null && $span['player'] === $event->player_name)
                        {
                            $spans[$resultId][$i]['end'] = eventTimeToSeconds($event->time);
                        }
                    }
                }
            }
        }

        return $spans;
    }

    /**
     * The keeper on the field at a given time.
     *
     * @param  array  $spans
     * @param  string $time
     * @return string|null
     */
    private function keeperAt(array $spans, $time)
    {
        $secs = eventTimeToSeconds($time);

        foreach ($spans as $span)
        {
            if ($secs >= $span['start'] && ($span['end'] === null || $secs <= $span['end']))
            {
                return $span['player'];
            }
        }

        return null;
    }
}
