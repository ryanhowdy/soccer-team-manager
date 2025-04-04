<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Season;
use App\Models\ClubTeamSeason;
use App\Models\ClubTeam;

class SeasonController extends Controller
{
    /**
     * store
     *
     * @return Illuminate\View\View
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'season' => ['required', 'max:50'],
            'year'   => ['required', 'date_format:Y'],
        ]);

        // Create the new season
        $season = new Season;

        $season->season          = $request->season;
        $season->year            = $request->year;
        $season->created_user_id = Auth()->user()->id;
        $season->updated_user_id = Auth()->user()->id;

        $season->save();

        // Get all managed teams
        $managedTeams = ClubTeam::where('managed', 1)
            ->get();

        // Create new team seasons for each managed team
        foreach ($managedTeams as $team)
        {
            $teamSeason = new ClubTeamSeason;

            $teamSeason->club_team_id    = $team->id;
            $teamSeason->season_id       = $season->id;
            $teamSeason->created_user_id = Auth()->user()->id;
            $teamSeason->updated_user_id = Auth()->user()->id;

            $teamSeason->save();
        }

        if ($request->wantsJson())
        {
            return response()->json([
                'success' => true,
                'data'    => $season->toArray(),
            ], 200);
        }

        return redirect()->route('rosters.index');
    }
}
