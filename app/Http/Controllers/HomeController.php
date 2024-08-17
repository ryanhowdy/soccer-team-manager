<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\Competition;
use App\Models\ResultEvent;
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
        $firstUser = User::first();

        if (is_null($firstUser))
        {
            return redirect()->route('register');
        }
        else if (!Auth()->user())
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
    public function home($teamId = null)
    {
        // Get all scheduled games for today
        $todayStart = \Carbon\Carbon::now()->startOfDay();
        $todayEnd   = \Carbon\Carbon::now()->endOfDay();

        $scheduledTeamIds = [];

        $scheduledToday = Result::with('competition')
            ->with('location')
            ->where('status', 'S')
            ->whereBetween('date', [$todayStart, $todayEnd])
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

        $lastResultsByTeam = [];
        foreach ($lastResults as $r)
        {
            $badGuys = $r->homeTeam->managed == 0 ? 'home' : 'away';

            $lastResultsByTeam[$r->{$badGuys . 'Team'}->id][] = $r;
        }

        // Get all managed teams
        $managedTeams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get();

        // Get selected managed team
        $selectedManagedTeam = [];
        if ($teamId)
        {
            $selectedManagedTeam = $managedTeams->first(function ($item) use ($teamId) {
                return $item->id == $teamId;
            });
        }
        else
        {
            // Randomly select a managed team to show (if multiple)
            $selectedManagedTeam = $managedTeams->random();
        }

        // Get the most recent League competition for this team
        $competition = Competition::where('club_team_id', $selectedManagedTeam->id)
            ->where('type', 'League')
            ->orderByDesc('started_at')
            ->first();

        $activeCompetitionId = $competition->id;

        // Get all the results for the currently selected managed teams' most recent non tournament competition
        $results = Result::where('status', 'D')
            ->where('competition_id', $activeCompetitionId)
            ->orWhere(function (Builder $query) use ($selectedManagedTeam) {
                $query->where('home_team_id', $selectedManagedTeam->id)
                    ->where('away_team_id', $selectedManagedTeam->id);
            })
            ->get();

        // Figure out the chart data based on the results
        $chartData = [
            'wdl'     => ['w' => 0, 'd' => 0, 'l' => 0],
            'gpg'     => ['goals' => 0, 'games' => 0, 'gpg' => 0],
            'gapg'    => ['allowed' => 0, 'games' => 0, 'gapg' => 0],
            'goals'   => ['players' => [], 'labels' => '', 'data' => ''],
            'assists' => ['players' => [], 'labels' => '', 'data' => ''],
        ];

        $resultIds = [];

        foreach ($results as $result)
        {
            $resultIds[$result->id] = $result->id;

            $goodGuys = $result->home_team_id == $selectedManagedTeam->id ? 'home' : 'away';
            $badGuys  = $goodGuys === 'home'                              ? 'away' : 'home';

            // win/draw/loss
            if ($result->{$goodGuys . '_team_score'} > $result->{$badGuys . '_team_score'})
            {
                $chartData['wdl']['w']++;
            }
            else if ($result->{$goodGuys . '_team_score'} < $result->{$badGuys . '_team_score'})
            {
                $chartData['wdl']['l']++;
            }
            else
            {
                $chartData['wdl']['d']++;
            }

            // goal per game
            $chartData['gpg']['goals'] += $result->{$goodGuys . '_team_score'};
            $chartData['gpg']['games']++;

            // goals allowed per game
            $chartData['gapg']['allowed'] += $result->{$badGuys . '_team_score'};
            $chartData['gapg']['games']++;
        }

        if ($chartData['gpg']['games'])
        {
            $chartData['gpg']['gpg']   = round($chartData['gpg']['goals'] / $chartData['gpg']['games'], 2);
        }
        if ($chartData['gapg']['games'])
        {
            $chartData['gapg']['gapg'] = round($chartData['gapg']['allowed'] / $chartData['gapg']['games'], 2);
        }

        // Get player goals/assists
        $events = ResultEvent::whereIn('result_id', $resultIds)
            ->where('event_id', Event::goal->value)
            ->get();

        foreach ($events as $event)
        {
            // goals
            if (!isset($chartData['goals']['players'][$event->player_name]))
            {
                $chartData['goals']['players'][$event->player_name] = 0;
            }

            $chartData['goals']['players'][$event->player_name]++;

            // assists
            if (!empty($event->additional))
            {
                if (!isset($chartData['assists']['players'][$event->player_name]))
                {
                    $chartData['assists']['players'][$event->player_name] = 0;
                }

                $chartData['assists']['players'][$event->player_name]++;
            }

        }

        // Sort goals and assits descending, then by player name alphabetical
        array_multisort(array_values($chartData['goals']['players']), SORT_DESC, array_keys($chartData['goals']['players']), SORT_ASC, $chartData['goals']['players']);
        array_multisort(array_values($chartData['assists']['players']), SORT_DESC, array_keys($chartData['assists']['players']), SORT_ASC, $chartData['assists']['players']);

        $chartData['goals']['labels'] = "'" . implode("','", array_keys($chartData['goals']['players'])) . "'";
        $chartData['goals']['data']   = implode(',', array_values($chartData['goals']['players']));
        $chartData['assists']['labels'] = "'" . implode("','", array_keys($chartData['assists']['players'])) . "'";
        $chartData['assists']['data']   = implode(',', array_values($chartData['assists']['players']));

        return view('home', [
            'scheduledToday'          => $scheduledToday,
            'scheduled'               => $scheduled,
            'lastResultsByTeam'       => $lastResultsByTeam,
            'managedTeams'            => $managedTeams,
            'competition'             => $competition,
            'selectedManagedTeamId'   => $selectedManagedTeam->id,
            'selectedManagedTeamName' => $selectedManagedTeam->name,
            'results'                 => $results,
            'chartData'               => $chartData,
        ]);
    }
}
