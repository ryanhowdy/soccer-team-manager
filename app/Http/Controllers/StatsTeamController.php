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
        $query = Result::query()->where('status', 'D');

        // Any filters
        $managedTeamId = $request->has('filter-managed') ? $request->input('filter-managed') : $managedTeams->keys()->first();
        $seasonId      = $request->has('filter-seasons') ? $request->input('filter-seasons') : $seasons->keys()->last();
        $teamId        = $request->has('filter-teams')   ? $request->input('filter-teams')   : null;

        // Any filters
        if (!empty($managedTeamId))
        {
            $query->where(function (Builder $q) use ($managedTeamId) {
                $q->where('home_team_id', $managedTeamId)
                    ->orWhere('away_team_id', $managedTeamId);
            });
        }
        if (!empty($seasonId))
        {
            // Turn the season_id into a club_team_season_id
            $clubTeamSeasonIds = ClubTeamSeason::where('season_id', $seasonId)
                ->get()
                ->pluck('id')
                ->toArray();

            $query->whereIn('club_team_season_id', $clubTeamSeasonIds);
        }
        if (!empty($teamId))
        {
            $query->where(function (Builder $q) use ($teamId) {
                $q->where('home_team_id', $teamId)
                    ->orWhere('away_team_id', $teamId);
            });
        }

        $results = $query->get();

        $chartData = \Chart::getData(['standard', 'homeaway'], $managedTeamId, $results);

        return view('stats.team', [
            'selectedManagedTeamId'   => $managedTeamId,
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
