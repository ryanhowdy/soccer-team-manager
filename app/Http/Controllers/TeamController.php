<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;
use App\Models\ClubTeam;

class TeamController extends Controller
{
    /**
     * Redirects to login or home page
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get all clubs
        // teams.club looks redundant, but it lets the ClubTeam display accessors
        // (cohort_label / rank_label) read the club type without firing a lookup
        // per team - Eloquent resolves it in one extra query.
        $clubs = Club::with('teams.club')
            ->orderBy('name')
            ->get();

        return view('teams.index', [
            // Only the clubs we manage a team for; the view then shows just that
            // club's managed teams.  Everything else is on the Opponents page.
            'myClubs'          => $clubs->filter(fn ($club) => clubHasManagedTeam($club)),
            // The create-team form offers every club, not just ours
            'clubs'            => $clubs,
            'createTeamAction' => route('teams.store'),
            'createClubAction' => route('clubs.store'),
        ]);
    }

    /**
     * First - when no managed club/team exists, show them this page
     * to help create the first one.
     *
     * @return Illuminate\View\View
     */
    public function first()
    {
        $clubs = Club::orderBy('name')
            ->get();

        if ($clubs->isEmpty())
        {
            return redirect()->route('clubs.first');
        }

        session(['first' => 'team']);

        return view('teams.first', [
            'clubs'            => $clubs,
            'createTeamAction' => route('teams.store'),
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
        // The two team types identify themselves differently, so the required
        // fields swap. A club team is an age cohort: birth year identifies it and
        // rank (A/B/C/D) optionally splits a big club's several teams of the same
        // age. A high school team IS its tier - varsity, JV or freshmen - so rank
        // is the identity and birth year means nothing, because the squad mixes
        // ages.
        $club     = Club::find($request->club_id);
        $isSchool = $club && $club->isSchool();

        $validated = $request->validate([
            'club_id'    => 'required|exists:clubs,id',
            'managed'    => 'sometimes|accepted',
            'name'       => 'required|string|max:255',
            'birth_year' => $isSchool ? ['nullable', 'date_format:Y'] : ['required', 'date_format:Y'],
            // Only three tiers exist for a school, so 'D' is not accepted there
            'rank'       => $isSchool ? ['required', 'in:A,B,C'] : ['nullable', 'in:A,B,C,D'],
            'website'    => 'nullable|string|max:255',
            'notes'      => 'nullable|string|max:255',
        ]);

        $team = new ClubTeam;

        if ($request->has('rank'))
        {
            $team->rank = $request->rank;
        }
        if ($request->has('website'))
        {
            $team->website = $request->website;
        }
        if ($request->has('notes'))
        {
            $team->notes = $request->notes;
        }

        $team->club_id         = $request->club_id;
        $team->managed         = $request->has('managed') ? 1 : 0;
        $team->name            = $request->name;
        // A hidden <input> still posts, so the form alone cannot be trusted to
        // keep a stale birth year out of a school team - enforce it here.
        $team->birth_year      = (!$isSchool && $request->filled('birth_year')) ? $request->birth_year : null;
        $team->created_user_id = Auth()->user()->id;
        $team->updated_user_id = Auth()->user()->id;

        $team->save();

        // Whether this came from the first-run "create your first team" screen
        $isFirstFlow = $request->session()->get('first') === 'team';

        if ($team->managed)
        {
            $request->session()->forget('first');

            // After creating the first managed team, drop the user straight into
            // the roster page to start building the squad.
            if ($isFirstFlow)
            {
                return redirect()->route('rosters.index');
            }
        }

        // Managed teams list on Teams, the rest on Opponents — land on whichever
        // page the new team actually shows up on.
        return redirect()->route($team->managed ? 'teams.index' : 'opponents.index');
    }

    /**
     * edit
     * 
     * @param int $id
     * @return Illuminate\View\View
     */
    public function edit($id)
    {
        $team = ClubTeam::find($id);

        // Get all clubs
        // teams.club looks redundant, but it lets the ClubTeam display accessors
        // (cohort_label / rank_label) read the club type without firing a lookup
        // per team - Eloquent resolves it in one extra query.
        $clubs = Club::with('teams.club')
            ->orderBy('name')
            ->get();

        return view('teams.edit', [
            'team'  => $team,
            'clubs' => $clubs,
        ]);
    }

    /**
     * update
     * 
     * @param int     $id
     * @param Request $request 
     * @return null
     */
    public function update($id, Request $request)
    {
        // The two team types identify themselves differently, so the required
        // fields swap. A club team is an age cohort: birth year identifies it and
        // rank (A/B/C/D) optionally splits a big club's several teams of the same
        // age. A high school team IS its tier - varsity, JV or freshmen - so rank
        // is the identity and birth year means nothing, because the squad mixes
        // ages.
        $club     = Club::find($request->club_id);
        $isSchool = $club && $club->isSchool();

        $validated = $request->validate([
            'club_id'    => 'required|exists:clubs,id',
            'managed'    => 'sometimes|accepted',
            'name'       => 'required|string|max:255',
            'birth_year' => $isSchool ? ['nullable', 'date_format:Y'] : ['required', 'date_format:Y'],
            // Only three tiers exist for a school, so 'D' is not accepted there
            'rank'       => $isSchool ? ['required', 'in:A,B,C'] : ['nullable', 'in:A,B,C,D'],
            'website'    => 'nullable|string|max:255',
            'notes'      => 'nullable|string|max:255',
        ]);

        $team = ClubTeam::find($id);

        if ($request->has('rank'))
        {
            $team->rank = $request->rank;
        }
        if ($request->has('website'))
        {
            $team->website = $request->website;
        }
        if ($request->has('notes'))
        {
            $team->notes = $request->notes;
        }

        $team->club_id         = $request->club_id;
        $team->managed         = $request->has('managed') ? 1 : 0;
        $team->name            = $request->name;
        // A hidden <input> still posts, so the form alone cannot be trusted to
        // keep a stale birth year out of a school team - enforce it here.
        $team->birth_year      = (!$isSchool && $request->filled('birth_year')) ? $request->birth_year : null;
        $team->updated_user_id = Auth()->user()->id;

        $team->save();

        // Ticking/unticking "Managed" moves the team between the two pages, so
        // follow it rather than always returning to Teams.
        return redirect()->route($team->managed ? 'teams.index' : 'opponents.index');
    }
}
