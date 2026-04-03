<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\ClubTeamSeason;
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
        // make sure we have at least 1 registered user
        $firstUser = User::first();

        if (is_null($firstUser))
        {
            return redirect()->route('register');
        }

        // make sure we have at least 1 managed team
        $managedTeam = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
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

        // Get the most recent competition of each type with their completed results
        $resultsByCompetition = collect();

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
        }

        return view('home', [
            'scheduledToday'         => $scheduledToday,
            'scheduled'              => $scheduled,
            'lastResultsAgainstTeam' => $lastResultsAgainstTeam,
            'resultsByCompetition'   => $resultsByCompetition,
        ]);
    }

    /**
     * update the currently selected team
     *
     * @return Illuminate\View\View
     */
    public function pickTeam($teamId)
    {
        $team = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->where('t.id', '=', $teamId)
            ->first();

        if ($team)
        {
            Auth()->user()->update(['selected_club_team_id' => $team->id]);
        }

        return back();
    }
}
