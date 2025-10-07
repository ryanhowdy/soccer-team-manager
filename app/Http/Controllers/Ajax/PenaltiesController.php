<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenaltyShootout;
use App\Models\Penalty;
use App\Enums\Event as EnumEvent;

class PenaltiesController extends Controller
{
    /**
     * start
     * 
     * @param Request $request 
     * @return json
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'result_id'     => 'required|exists:results,id',
            'first_team_id' => 'required|exists:club_teams,id',
        ]);

        $pk = new PenaltyShootout;

        $pk->result_id       = $request->result_id;
        $pk->first_team_id   = $request->first_team_id;
        $pk->created_user_id = Auth()->user()->id;
        $pk->updated_user_id = Auth()->user()->id;

        $pk->save();

        return response()->json([
            'success' => true,
            'data'    => $pk->toArray(),
        ], 200);
    }

    /**
     * store
     * 
     * @param Request $request 
     * @return json
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'penalty_shootout_id' => 'required|exists:penalty_shootouts,id',
            'player_id'           => 'sometimes|exists:players,id',
            'event_id'            => 'required|integer',
            'against'             => 'sometimes|integer',
        ]);

        $pk = new Penalty;

        $pk->penalty_shootout_id = $request->penalty_shootout_id;
        $pk->event_id            = $request->event_id;

        if ($request->filled('player_id'))
        {
            $pk->player_id = $request->player_id;
        }
        if ($request->filled('against'))
        {
            $pk->against = $request->against;
        }

        $pk->save();

        return response()->json([
            'success' => true,
            'data'    => $pk->toArray(),
        ], 200);
    }
}
