<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\Competition;
use App\Enums\ResultStatus;

class StatsCompetitionController extends Controller
{
    /**
     * index
     *
     * The "By Competition" stats landing.  Lists the selected team's
     * competitions for the selected season, each linking to its report.
     *
     * @param Request $request
     * @return null
     */
    public function index(Request $request)
    {
        $seasons = Season::newestFirst()->get()->keyBy('id');

        $selectedSeason = resolveSeasonFilter($request, $seasons);

        $competitions = Competition::where('club_team_id', auth()->user()->selected_club_team_id)
            ->when($selectedSeason, fn ($q) => $q->whereYear('started_at', $selectedSeason->year))
            ->orderBy('started_at', 'desc')
            ->get();

        // Record (w/d/l) per competition
        $results = Result::whereIn('competition_id', $competitions->pluck('id'))
            ->where('status', ResultStatus::Done->value)
            ->with('homeTeam')
            ->get();

        $recordsByCompetition = [];

        foreach ($results as $result)
        {
            if (!isset($recordsByCompetition[$result->competition_id]))
            {
                $recordsByCompetition[$result->competition_id] = ['W' => 0, 'D' => 0, 'L' => 0];
            }

            $recordsByCompetition[$result->competition_id][ $result->win_draw_loss ]++;
        }

        // For the shared create-competition modal
        $managedTeams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get();

        return view('stats.competitions.index', [
            'competitions'         => $competitions->groupBy('status'),
            'recordsByCompetition' => $recordsByCompetition,
            'seasons'              => $seasons,
            'selectedSeason'       => $selectedSeason,
            'managedTeams'         => $managedTeams,
        ]);
    }
}
