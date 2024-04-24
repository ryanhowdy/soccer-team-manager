<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\Competition;
use Illuminate\Database\Eloquent\Builder;

class StatsCompetitionController extends Controller
{
    public function index()
    {
        // Get all competitions
        $leagues = Competition::where('type', 'League')
            ->orderBy('started_at', 'desc')
            ->get();

        $cups = Competition::where('type', 'Cup')
            ->orderBy('started_at', 'desc')
            ->get();

        return view('stats.competitions', [
            'leagues' => $leagues,
            'cups'    => $cups,
        ]);
    }

    public function show(Request $request, $competitionId)
    {
        // Get the selected competition
        $selectedCompetition = Competition::find($competitionId);

        // Get all competitions
        $competitions = Competition::where('club_team_id', $selectedCompetition->club_team_id)
            ->orderBy('type')
            ->orderBy('started_at', 'desc')
            ->get();

        // Get all the results for this competition
        $results = Result::query()->where('competition_id', $selectedCompetition->id)
            ->get();

        $chartData = getChartDataFromResults($results, $selectedCompetition->club_team_id);

        return view('stats.competition', [
            'selectedCompetition' => $selectedCompetition,
            'competitions'        => $competitions,
            'results'             => $results,
            'chartData'           => $chartData,
        ]);
    }
}
