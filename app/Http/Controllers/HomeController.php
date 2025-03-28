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

        $managedTeam = ClubTeam::first();

        if (is_null($managedTeam))
        {
            return redirect()->route('teams.index')->withErrors(['You must create at least 1 managed team.']);
        }

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
    public function home($teamId = null)
    {
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

        if ($managedTeams->count() <= 0)
        {
            return redirect()->route('teams.index')->withErrors(['You must create at least 1 managed team.']);
        }

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

        $results = new \Illuminate\Database\Eloquent\Collection();

        if (!is_null($competition))
        {
            $activeCompetitionId = $competition->id;

            // Get all the results for the currently selected managed teams' most recent non tournament competition
            $results = Result::where('status', 'D')
                ->where('competition_id', $activeCompetitionId)
                ->orWhere(function (Builder $query) use ($selectedManagedTeam) {
                    $query->where('home_team_id', $selectedManagedTeam->id)
                        ->where('away_team_id', $selectedManagedTeam->id);
                })
                ->get();
        }

        // Figure out the chart data based on the results
        $chartData = \Chart::getData(['standard'], $selectedManagedTeam->id, $results);

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
