<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\ClubTeam;
use App\Models\ClubTeamSeason;
use App\Models\Competition;
use App\Models\ResultEvent;
use App\Models\PlayerGameRating;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Event;

class HomeController extends Controller
{
    /**
     * Redirects to login or home page
     *
     * @return Illuminate\View\View
     */
    public function index()
    {
        // make sure we have at least 1 registered user
        $firstUser = User::first();

        if (is_null($firstUser))
        {
            return redirect()->route('register');
        }

        // make sure we have at least 1 managed team
        $managedTeam = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name', 'c.type as club_type')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->first();

        if (is_null($managedTeam))
        {
            return redirect()->route('clubs.first');
        }

        // make sure the user is logged in
        if (!Auth()->user())
        {
            return redirect()->route('login');
        }

        return redirect()->route('home');
    }

    /**
     * Display the home view
     *
     * @return Illuminate\View\View
     */
    public function home()
    {
        $selectedTeamId = Auth()->user()->selected_club_team_id;

        // Get all scheduled games for today
        $todayStart = \Carbon\Carbon::now()->inUserTimezone()->startOfDay()->tz('UTC');
        $todayEnd   = \Carbon\Carbon::now()->inUserTimezone()->endOfDay()->tz('UTC');

        $scheduledTeamIds = [];

        $scheduledToday = Result::with('competition')
            ->with('location')
            ->where('status', 'S')
            ->whereBetween('date', [$todayStart, $todayEnd])
            ->orderBy('date')
            ->get();

        foreach ($scheduledToday as $s)
        {
            if ($s->homeTeam->managed == 0)
            {
                $scheduledTeamIds[] = $s->homeTeam->id;
            }
            if ($s->awayTeam->managed == 0)
            {
                $scheduledTeamIds[] = $s->awayTeam->id;
            }
        }

        // Get all future scheduled games (not today)
        $scheduled = Result::with('competition')
            ->with('location')
            ->where('status', 'S')
            ->whereNotBetween('date', [$todayStart, $todayEnd])
            ->orderBy('date')
            ->get();

        foreach ($scheduled as $s)
        {
            if ($s->homeTeam->managed == 0)
            {
                $scheduledTeamIds[] = $s->homeTeam->id;
            }
            if ($s->awayTeam->managed == 0)
            {
                $scheduledTeamIds[] = $s->awayTeam->id;
            }
        }

        // Get the last 5 results for each team we are scheduled to play
        $lastResults = Result::where('status', 'D')
            ->where(function (Builder $query) use ($scheduledTeamIds) {
                $query->whereIn('home_team_id', $scheduledTeamIds)
                    ->orWhereIn('away_team_id', $scheduledTeamIds);
            })
            ->orderBy('date')
            ->get();

        $lastResultsAgainstTeam = [];
        foreach ($lastResults as $r)
        {
            $badGuys = $r->homeTeam->managed == 0 ? 'home' : 'away';

            $lastResultsAgainstTeam[$r->{$badGuys . 'Team'}->id][] = $r;
        }

        // Get the most recent season for the selected team
        $latestSeason = ClubTeamSeason::where('club_team_id', $selectedTeamId)
            ->orderBy('id', 'desc')
            ->first();

        // Seed the shared season filter from the team's latest season, so the
        // season-filtered pages open on the same season the dashboard is showing.
        // 'all' is a legitimate stored value, so this checks for the key being
        // absent rather than empty.  With no team season yet there's nothing to
        // seed, and resolveSeasonFilter() falls back to the newest season.
        if ($latestSeason && !session()->has('selected_season_id'))
        {
            session(['selected_season_id' => $latestSeason->season_id]);
        }

        // Get the most recent competition of each type with their completed results
        $resultsByCompetition = collect();
        $allSeasonResults = collect();

        if ($latestSeason)
        {
            $competitionIds = Result::where('club_team_season_id', $latestSeason->id)
                ->distinct()
                ->pluck('competition_id');

            $resultsByCompetition = Competition::whereIn('id', $competitionIds)
                ->with(['results' => function ($query) use ($selectedTeamId) {
                    $query->where('status', 'D')
                        ->where(function (Builder $query) use ($selectedTeamId) {
                            $query->where('home_team_id', $selectedTeamId)
                                ->orWhere('away_team_id', $selectedTeamId);
                        })
                        ->orderBy('date', 'desc');
                }])
                ->orderBy('started_at', 'desc')
                ->get();

            // Collect all completed results for this season (for dashboard widgets)
            $allSeasonResults = Result::with('formation')
                ->where('club_team_season_id', $latestSeason->id)
                ->where('status', 'D')
                ->where(function (Builder $query) use ($selectedTeamId) {
                    $query->where('home_team_id', $selectedTeamId)
                        ->orWhere('away_team_id', $selectedTeamId);
                })
                ->orderBy('date')
                ->get();
        }

        // Build dashboard stats from season results
        $dashboard = $this->buildDashboardStats($allSeasonResults, $selectedTeamId);

        return view('home', [
            'scheduledToday'         => $scheduledToday,
            'scheduled'              => $scheduled,
            'lastResultsAgainstTeam' => $lastResultsAgainstTeam,
            'resultsByCompetition'   => $resultsByCompetition,
            'dashboard'              => $dashboard,
            'latestSeason'           => $latestSeason,
        ]);
    }

    /**
     * Build all dashboard stats from season results
     */
    private function buildDashboardStats($results, $teamId)
    {
        $dashboard = [
            'formStreak'      => [],
            'seasonRecord'    => ['wins' => 0, 'draws' => 0, 'losses' => 0, 'games' => 0, 'goals' => 0, 'goals_against' => 0, 'clean_sheets' => 0, 'win_percent' => 0],
            'homeAway'        => [
                'home' => ['wins' => 0, 'draws' => 0, 'losses' => 0, 'games' => 0, 'goals' => 0, 'goals_against' => 0],
                'away' => ['wins' => 0, 'draws' => 0, 'losses' => 0, 'games' => 0, 'goals' => 0, 'goals_against' => 0],
            ],
            'topScorers'      => [],
            'topAssisters'    => [],
            'topRated'        => [],
            'topChances'      => [],
            'formations'      => [],
            'goalTiming'      => array_fill(0, 6, ['for' => 0, 'against' => 0]),
        ];

        if ($results->isEmpty())
        {
            return $dashboard;
        }

        $resultIds = [];

        foreach ($results as $result)
        {
            $resultIds[] = $result->id;

            $goodGuys = $result->home_team_id == $teamId ? 'home' : 'away';
            $badGuys  = $goodGuys === 'home' ? 'away' : 'home';

            $usGoals   = $result->{$goodGuys . '_team_score'};
            $themGoals = $result->{$badGuys . '_team_score'};

            // Form streak (last 10)
            $dashboard['formStreak'][] = $result->win_draw_loss;

            // Season record
            $dashboard['seasonRecord']['games']++;
            $dashboard['seasonRecord']['goals'] += $usGoals;
            $dashboard['seasonRecord']['goals_against'] += $themGoals;

            if ($themGoals == 0)
            {
                $dashboard['seasonRecord']['clean_sheets']++;
            }

            if ($usGoals > $themGoals)
            {
                $dashboard['seasonRecord']['wins']++;
                $dashboard['homeAway'][$goodGuys]['wins']++;
            }
            else if ($usGoals < $themGoals)
            {
                $dashboard['seasonRecord']['losses']++;
                $dashboard['homeAway'][$goodGuys]['losses']++;
            }
            else
            {
                $dashboard['seasonRecord']['draws']++;
                $dashboard['homeAway'][$goodGuys]['draws']++;
            }

            $dashboard['homeAway'][$goodGuys]['games']++;
            $dashboard['homeAway'][$goodGuys]['goals'] += $usGoals;
            $dashboard['homeAway'][$goodGuys]['goals_against'] += $themGoals;

            // Formation effectiveness
            if ($result->formation_id)
            {
                $formName = $result->formation ? $result->formation->name : 'Unknown';

                if (!isset($dashboard['formations'][$formName]))
                {
                    $dashboard['formations'][$formName] = ['wins' => 0, 'draws' => 0, 'losses' => 0, 'games' => 0, 'goals' => 0, 'goals_against' => 0];
                }

                $dashboard['formations'][$formName]['games']++;
                $dashboard['formations'][$formName]['goals'] += $usGoals;
                $dashboard['formations'][$formName]['goals_against'] += $themGoals;

                if ($usGoals > $themGoals) $dashboard['formations'][$formName]['wins']++;
                else if ($usGoals < $themGoals) $dashboard['formations'][$formName]['losses']++;
                else $dashboard['formations'][$formName]['draws']++;
            }
        }

        // Win percent
        if ($dashboard['seasonRecord']['games'])
        {
            $dashboard['seasonRecord']['win_percent'] = round(($dashboard['seasonRecord']['wins'] / $dashboard['seasonRecord']['games']) * 100);
        }

        // Reverse form streak so most recent is last, then take last 10
        $dashboard['formStreak'] = array_slice($dashboard['formStreak'], -10);

        // Get events for all season results
        $goalEvents   = Event::getGoalValues();
        $assistEvents = Event::getAssistValues();
        $chanceEvents = Event::getChanceValues();

        $events = ResultEvent::whereIn('result_id', $resultIds)->get();

        $scorers   = [];
        $assisters = [];
        $chances   = [];

        foreach ($events as $event)
        {
            // Goal timing (15-min buckets)
            if (in_array($event->event_id, $goalEvents))
            {
                $secs = eventTimeToSeconds($event->time);
                $bucket = min((int) floor($secs / 900), 5);

                if ($event->against)
                {
                    $dashboard['goalTiming'][$bucket]['against']++;
                }
                else
                {
                    $dashboard['goalTiming'][$bucket]['for']++;
                }
            }

            // Skip opponent events for remaining stats
            if ($event->against)
            {
                continue;
            }

            // Top scorers
            if (in_array($event->event_id, $goalEvents))
            {
                $name = $event->player_name;
                $scorers[$name] = ($scorers[$name] ?? 0) + 1;
            }

            // Top assisters.  Not every goal can be assisted - see
            // Event::getAssistValues().
            if (in_array($event->event_id, $assistEvents))
            {
                if (!empty($event->additional) && $event->additionalPlayer)
                {
                    $aName = $event->additionalPlayer->name;
                    $assisters[$aName] = ($assisters[$aName] ?? 0) + 1;
                }
            }

            // Chance creation - the pass that set up a goal or shot.  Reads the
            // same Event::getChanceValues() the 'Cha' column on stats/teams
            // counts, so the two can never disagree.
            if (in_array($event->event_id, $chanceEvents))
            {
                if (!empty($event->additional) && $event->additionalPlayer)
                {
                    $cName = $event->additionalPlayer->name;
                    $chances[$cName] = ($chances[$cName] ?? 0) + 1;
                }
            }
        }

        // Player ratings. Several users can rate the same player in the same
        // game, so a game's rating is the average of those, and the player's
        // season rating is the average of their rated games. Averaging the raw
        // rows instead would weight a game by how many people bothered to rate it.
        $rated = [];

        $ratingRows = PlayerGameRating::with('player')
            ->whereIn('result_id', $resultIds)
            ->get();

        foreach ($ratingRows->groupBy('player_id') as $playerRows)
        {
            $gameRatings = $playerRows->groupBy('result_id')
                ->map(fn ($rows) => $rows->avg('rating'));

            $player = $playerRows->first()->player;

            $rated[$player ? $player->name : 'Unknown'] = [
                'rating' => round($gameRatings->avg(), 1),
                'games'  => $gameRatings->count(),
            ];
        }

        // Sort and take top 5
        arsort($scorers);
        arsort($assisters);
        arsort($chances);
        uasort($rated, fn ($a, $b) => $b['rating'] <=> $a['rating']);

        $dashboard['topScorers']   = array_slice($scorers, 0, 5, true);
        $dashboard['topAssisters'] = array_slice($assisters, 0, 5, true);
        $dashboard['topChances']   = array_slice($chances, 0, 5, true);
        $dashboard['topRated']     = array_slice($rated, 0, 5, true);

        return $dashboard;
    }

    /**
     * update the currently selected team
     *
     * @return Illuminate\View\View
     */
    public function pickTeam($teamId, Request $request)
    {
        $team = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name', 'c.type as club_type')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->where('t.id', '=', $teamId)
            ->first();

        if ($team)
        {
            Auth()->user()->update(['selected_club_team_id' => $team->id]);
        }

        // Manage -> Teams & Clubs links straight into a team's own pages, but those
        // are scoped by the picker rather than by the url, so the switch has to
        // happen first.  Only the team-scoped landings are accepted as targets;
        // anything else (the navbar picker included) just returns where it came from.
        $allowedTargets = ['home', 'games.index', 'rosters.index'];

        if (in_array($request->input('to'), $allowedTargets, true))
        {
            return redirect()->route($request->input('to'));
        }

        return back();
    }
}
