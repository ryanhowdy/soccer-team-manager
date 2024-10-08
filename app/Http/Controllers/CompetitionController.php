<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\Competition;
use App\Enums\ResultStatus;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CompetitionController extends Controller
{
    /**
     * index 
     * 
     * @return null
     */
    public function index()
    {
        // Get all competitions
        $leagues = Competition::where('type', 'League')
            ->orderBy('started_at', 'desc')
            ->get();

        $cups = Competition::where('type', 'Cup')
            ->orderBy('started_at', 'desc')
            ->get();

        $friendlys = Competition::where('type', 'Friendly')
            ->orderBy('started_at', 'desc')
            ->get();

        $managedTeams = ClubTeam::from('club_teams as t')
            ->select('t.*', 'c.name as club_name')
            ->join('clubs as c', 't.club_id', '=', 'c.id')
            ->where('managed', 1)
            ->orderBy('club_name')
            ->orderBy('t.name')
            ->get();

        return view('competitions.index', [
            'leagues'   => $leagues,
            'cups'      => $cups,
            'friendlys' => $friendlys,
            'action'    => route('competitions.store'),
            'teams'     => $managedTeams,
        ]);
    }

    /**
     * store 
     * 
     * @param Request $request 
     * @return null
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'club_team_id'      => 'required|exists:club_teams,id',
            'type'              => 'required|in:Cup,Friendly,League',
            'division'          => 'required|string|max:255',
            'place'             => 'nullable|integer',
            'level'             => 'nullable|integer',
            'total_levels'      => 'nullable|integer',
            'started_at'        => 'required|date_format:Y-m-d',
            'ended_at'          => 'required|date_format:Y-m-d',
            'website'           => 'nullable|string|max:255',
            'notes'             => 'nullable|string|max:255',
        ]);

        $competition = new Competition;

        $competition->name            = $request->name;
        $competition->club_team_id    = $request->club_team_id;
        $competition->type            = $request->type;
        $competition->division        = $request->division;
        $competition->place           = $request->place;
        $competition->level           = $request->level;
        $competition->total_levels    = $request->total_levels;
        $competition->started_at      = Carbon::parse($request->started_at);
        $competition->ended_at        = Carbon::parse($request->ended_at);
        $competition->website         = $request->website;
        $competition->notes           = $request->notes;
        $competition->created_user_id = Auth()->user()->id;
        $competition->updated_user_id = Auth()->user()->id;

        $competition->save();

        return redirect()->route('competitions.index');
    }


    /**
     * show 
     * 
     * @param Competition $competition 
     * @param Request $request 
     * @return null
     */
    public function show(Competition $competition, Request $request)
    {
        // Get all the results for this competition
        $results = Result::where('competition_id', $competition->id)
            ->where('status', ResultStatus::Done->value)
            ->get();

        $chartData = getChartDataFromResults($results, $competition->club_team_id);

        return view('competitions.show', [
            'selectedCompetition' => $competition,
            'results'             => $results,
            'chartData'           => $chartData,
        ]);
    }
}
