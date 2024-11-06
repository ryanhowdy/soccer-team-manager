<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Competition;
use App\Enums\CompetitionStatus as Status;

class CompetitionController extends Controller
{
    /**
     * update 
     * 
     * @param Competition $comp 
     * @param Request $request 
     * @return null
     */
    public function update(Competition $competition, Request $request)
    {
        $validated = $request->validate([
            'place' => 'nullable|integer',
        ]);

        if ($request->filled('place'))
        {
            $competition->place  = $request->place;
            $competition->status = Status::Done->value;
        }

        $competition->updated_user_id = Auth()->user()->id;
        $competition->save();

        return response()->json([
            'success' => true,
            'data'    => $competition->toArray(),
        ], 200);
    }
}
