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
        $clubs = Club::with('teams')
            ->orderBy('name')
            ->get();

        return view('teams', [
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
        $validated = $request->validate([
            'club_id'    => 'required|exists:clubs,id',
            'managed'    => 'sometimes|accepted',
            'name'       => 'required|string|max:255',
            'birth_year' => 'required|date_format:Y',
            'rank'       => 'nullable|in:A,B,C,D',
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
        $team->birth_year      = $request->birth_year;
        $team->created_user_id = Auth()->user()->id;
        $team->updated_user_id = Auth()->user()->id;

        $team->save();

        if ($team->managed)
        {
            $request->session()->forget('first');
        }

        return redirect()->route('teams.index');
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
        $clubs = Club::with('teams')
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
        $validated = $request->validate([
            'club_id'    => 'required|exists:clubs,id',
            'managed'    => 'sometimes|accepted',
            'name'       => 'required|string|max:255',
            'birth_year' => 'required|date_format:Y',
            'rank'       => 'nullable|in:A,B,C,D',
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
        $team->birth_year      = $request->birth_year;
        $team->updated_user_id = Auth()->user()->id;

        $team->save();

        return redirect()->route('teams.index');
    }
}
