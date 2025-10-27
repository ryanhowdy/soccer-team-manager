<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClubTeamSeason;

class ClubTeamSeasonController extends Controller
{
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

