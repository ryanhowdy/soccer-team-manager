<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\ResultEvent;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Event;

class StatsTeamController extends Controller
{
    public function index()
    {
        // We don't show a team stats listing, so lets just look up all the 
        // managed teams and pick one at random, then redirect to that teams
        // stats page.

        // Get all managed teams
        $managedTeams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get();

        $selectedManagedTeam = $managedTeams->random();

        return redirect()->route('stats.teams.show', ['id' => $selectedManagedTeam->id]);
    }

    public function show(Request $request, $managedTeamId)
    {
        // Get all managed teams
        $managedTeams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get();

        // Get selected managed team
        $selectedManagedTeam = $managedTeams->first(function ($item) use ($managedTeamId) {
            return $item->id == $managedTeamId;
        });

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

        // Get all the results for the currently selected filters
        $query = Result::query()->where('status', 'D')
            ->where(function (Builder $q) use ($selectedManagedTeam) {
                $q->where('home_team_id', $selectedManagedTeam->id)
                    ->orWhere('away_team_id', $selectedManagedTeam->id);
            });

        // Any filters
        $seasonId = $request->has('filter-seasons') ? $request->input('filter-seasons') : $seasons->keys()->last();
        $teamId   = $request->has('filter-teams')   ? $request->input('filter-teams')   : null;

        // Any filters
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

        $chartData = getChartDataFromResults($results, $selectedManagedTeam->id);

        return view('stats.team', [
            'selectedManagedTeam'     => $selectedManagedTeam,
            'selectedSeason'          => $seasonId,
            'selectedTeam'            => $teamId,
            'managedTeams'            => $managedTeams,
            'seasons'                 => $seasons,
            'teams'                   => $teamsByClub,
            'results'                 => $results,
            'chartData'               => $chartData,
        ]);
    }
}
