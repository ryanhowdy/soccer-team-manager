<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Result;
use App\Models\Season;
use App\Models\ClubTeam;
use App\Models\ClubTeamSeason;
use App\Models\Location;
use App\Models\ResultEvent;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Event;
use App\Enums\ResultStatus;

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

    /**
     * show
     *
     * @param Location $location
     * @param Request $request
     * @return null
     */
    public function show(Location $location, Request $request)
    {
        $clubTeamId = auth()->user()->selected_club_team_id;

        $results = Result::where('status', ResultStatus::Done->value)
            ->where('location_id', $location->id)
            ->where(function (Builder $q) use ($clubTeamId) {
                $q->where('home_team_id', $clubTeamId)
                    ->orWhere('away_team_id', $clubTeamId);
            })
            ->get();

        $chartData = \Chart::getData(['standard', 'homeaway'], $clubTeamId, $results);

        return view('stats.locations.show', [
            'selectedLocation' => $location,
            'results'          => $results,
            'chartData'        => $chartData,
        ]);
    }
}
