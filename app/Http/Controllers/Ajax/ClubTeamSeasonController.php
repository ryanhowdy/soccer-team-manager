<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClubTeamSeason;
use App\Models\ClubTeam;
use App\Models\Season;
use Illuminate\Validation\ValidationException;

class ClubTeamSeasonController extends Controller
{
    /**
     * store
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'season_id'    => ['required', 'integer', 'exists:seasons,id'],
            'club_team_id' => ['required', 'integer', 'exists:club_teams,id'],
        ]);

        // High school teams play a Fall season only. A player's grade is derived
        // from the season, and a Spring school season would read a year off, so
        // reject it rather than compute it wrong.
        $team   = ClubTeam::with('club')->find($request->club_team_id);
        $season = Season::find($request->season_id);

        if ($team && $team->isSchoolTeam() && !seasonIsFall($season))
        {
            throw ValidationException::withMessages([
                'season_id' => 'High school teams play a Fall season only.',
            ]);
        }

        // Don't create a duplicate link if this team is already in the season
        $exists = ClubTeamSeason::where('season_id', $request->season_id)
            ->where('club_team_id', $request->club_team_id)
            ->exists();

        if (!$exists)
        {
            $teamSeason = new ClubTeamSeason;

            $teamSeason->club_team_id    = $request->club_team_id;
            $teamSeason->season_id       = $request->season_id;
            $teamSeason->created_user_id = Auth()->user()->id;
            $teamSeason->updated_user_id = Auth()->user()->id;

            $teamSeason->save();
        }

        return response()->json(['success' => true], 200);
    }

    /**
     * destroy
     * 
     * @param ClubTeamSeason $season
     * @param Request $request 
     * @return null
     */
    public function destroy(ClubTeamSeason $season, Request $request)
    {
        $season->delete();

        return response()->noContent();
    }
}

