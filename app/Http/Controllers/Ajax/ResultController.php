<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Result;

class ResultController extends Controller
{
    /**
     * update
     * 
     * @param Result  $id 
     * @param Request $request 
     * @return json
     */
    public function update(Result $result, Request $request)
    {
        $validated = $request->validate([
            'season_id'         => 'nullable|exists:seasons,id',
            'competition_id'    => 'nullable|exists:competitions,id',
            'location_id'       => 'nullable|exists:locations,id',
            'date'              => 'nullable|date_format:Y-m-d H:i',
            'home_team_id'      => 'nullable|exists:club_teams,id',
            'away_team_id'      => 'nullable|exists:club_teams,id',
            'home_team_score'   => 'nullable|integer',
            'away_team_score'   => 'nullable|integer',
            'notes'             => 'nullable|string|max:255',
            'live'              => 'nullable|boolean',
            'formation_id'      => 'nullable|exists:formations,id',
            'status'            => 'nullable|in:S,C,D',
        ]);

        if ($request->filled('season_id'))
        {
            $result->season_id = $request->season_id;
        }
        if ($request->filled('notes'))
        {
            $result->notes = $request->notes;
        }
        if ($request->filled('live'))
        {
            $result->live = $request->live;
        }
        if ($request->filled('status'))
        {
            $result->status = $request->status;
        }

        $result->save();

        return response()->json([
            'success' => true,
            'data'    => $result->toArray(),
        ], 200);
    }
}
