<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\ClubTeamSeason;
use App\Models\ResultEvent;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Event;

class StatsLocationController extends Controller
{
    /**
     * index 
     * 
     * @param Request $request 
     * @return null
     */
    public function index(Request $request)
    {
        $results = Result::where('status', 'D')
            ->with('location')
            ->get();

        $resultsByTeamLocation = [];

        foreach ($results as $result)
        {
            $teamId = $result->home_team_id;
            if ($result->awayTeam->managed)
            {
                $teamId = $result->away_team_id;
            }

            if (!isset($resultsByTeamLocation[$teamId]))
            {
                $resultsByTeamLocation[$teamId] = [];
            }
            if (!isset($resultsByTeamLocation[$teamId][$result->location_id]))
            {
                $resultsByTeamLocation[$teamId][$result->location_id] = [];
            }

            $resultsByTeamLocation[$teamId][$result->location_id][] = $result;
        }

        return view('stats.locations.index', [
            'resultsByTeamLocation' => $resultsByTeamLocation,
        ]);
    }
}
