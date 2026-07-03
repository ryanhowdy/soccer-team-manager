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
            'season'  => ['required', 'max:50'],
            'year'    => ['required', 'date_format:Y'],
            'teams'   => ['nullable', 'array'],
            'teams.*' => ['integer', 'exists:club_teams,id'],
        ]);

        // Create the new season
        $season = new Season;

        $season->season          = $request->season;
        $season->year            = $request->year;
        $season->created_user_id = Auth()->user()->id;
        $season->updated_user_id = Auth()->user()->id;

        $season->save();

        // Only create team seasons for the teams the user selected. Teams can
        // always be added to (or removed from) the season later on the rosters
        // page, so we no longer force a record for every managed team.
        $teamIds = $request->input('teams', []);

        if (!empty($teamIds))
        {
            $managedTeams = ClubTeam::where('managed', 1)
                ->whereIn('id', $teamIds)
                ->get();

            foreach ($managedTeams as $team)
            {
                $teamSeason = new ClubTeamSeason;

                $teamSeason->club_team_id    = $team->id;
                $teamSeason->season_id       = $season->id;
                $teamSeason->created_user_id = Auth()->user()->id;
                $teamSeason->updated_user_id = Auth()->user()->id;

                $teamSeason->save();
            }
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
