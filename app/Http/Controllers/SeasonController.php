<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Season;
use App\Models\ClubTeamSeason;
use App\Models\ClubTeam;
use App\Enums\SeasonName;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SeasonController extends Controller
{
    /**
     * store
     *
     * @return Illuminate\View\View
     */
    public function store(Request $request)
    {
        // The form is a select, so this is normally a no-op. It keeps a stray
        // seeder or API call from being rejected over casing alone, and means
        // only the canonical value ever reaches the database.
        if ($request->filled('season'))
        {
            $request->merge(['season' => Str::title(trim($request->season))]);
        }

        $validated = $request->validate([
            'season'  => ['required', Rule::enum(SeasonName::class)],
            'year'    => ['required', 'date_format:Y'],
            'teams'   => ['nullable', 'array'],
            'teams.*' => ['integer', 'exists:club_teams,id'],
        ]);

        // High school teams play a Fall season only. A player's grade is derived
        // from the season, so a Spring school team-season would read a year off.
        // Reject loudly rather than silently dropping teams the user picked -
        // and do it before the season is created, so a rejected request doesn't
        // leave an orphan season behind.
        if (!seasonIsFall($request->season) && !empty($request->input('teams', [])))
        {
            $schoolTeams = ClubTeam::with('club')
                ->whereIn('id', $request->input('teams', []))
                ->get()
                ->filter(fn ($team) => $team->isSchoolTeam());

            if ($schoolTeams->isNotEmpty())
            {
                throw ValidationException::withMessages([
                    'teams' => 'High school teams play a Fall season only. Remove: '
                        . $schoolTeams->pluck('name')->implode(', ') . '.',
                ]);
            }
        }

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
