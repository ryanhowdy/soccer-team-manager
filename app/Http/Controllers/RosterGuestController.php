<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RosterGuest;

class RosterGuestController extends Controller
{
    /**
     * store
     * 
     * @param Request $request 
     * @return Illuminate\View\View
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'club_team_season_id' => 'required|exists:club_team_seasons,id',
            'result_id'           => 'required|exists:results,id',
            'player_id'           => 'required|integer|exists:players,id',
            'number'              => 'nullable|integer',
        ]);

        $guest = new RosterGuest;

        $guest->club_team_season_id = $request->club_team_season_id;
        $guest->result_id           = $request->result_id;
        $guest->player_id           = $request->player_id;
        $guest->created_user_id     = Auth()->user()->id;
        $guest->updated_user_id     = Auth()->user()->id;

        if ($request->filled('number'))
        {
            $guest->number = $request->number;
        }

        $guest->save();

        return redirect()->route('games.preview', ['id' => $guest->result_id]);
    }
}
