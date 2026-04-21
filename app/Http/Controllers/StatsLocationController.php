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
            ->where(function (Builder $q) {
                $q->where('home_team_id', auth()->user()->selected_club_team_id)
                    ->orWhere('away_team_id', auth()->user()->selected_club_team_id);
            })
            ->with('location')
            ->get();

        $resultsByLocation = [];

        foreach ($results as $result)
        {
            if (!isset($resultsByLocation[$result->location_id]))
            {
                $resultsByLocation[$result->location_id] = [];
            }

            $resultsByLocation[$result->location_id][] = $result;
        }

        return view('stats.locations.index', [
            'resultsByLocation' => $resultsByLocation,
        ]);
    }
}
