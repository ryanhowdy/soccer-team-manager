<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use App\Models\Result;
use App\Models\ResultEvent;
use App\Models\Formation;
use App\Models\Player;
use App\Models\ClubTeamSeason;
use App\Models\Event;

class LiveGameController extends Controller
{
    /**
     * Displays the live game view
     *
     * @return Illuminate\View\View
     */
    public function index(Request $request, int $id)
    {
        // Get the result info
        $result = Result::with('competition')
            ->with('location')
            ->with('homeTeam.club')
            ->with('awayTeam.club')
            ->find($id);

        // Figure out which teams are managed
        $managedTeam = [];

        if ($result->homeTeam->managed)
        {
            $managedTeam[] = $result->homeTeam->id;
        }
        if ($result->awayTeam->managed)
        {
            $managedTeam[] = $result->awayTeam->id;
        }

        // This result maybe in progress, get any previous result events
        $resultEvents = ResultEvent::where('result_id', $result->id)
            ->get();

        $clubTeamSeasonIds = ClubTeamSeason::whereIn('club_team_id', $managedTeam)
            ->where('season_id', $result->season_id)
            ->get()
            ->pluck('id')
            ->toArray();

        // get the players for any managed teams
        $players = Player::select('players.*', 'rosters.number')
            ->with('positions')
            ->orderBy('name')
            ->join('rosters', function (JoinClause $join) use ($clubTeamSeasonIds) {
                $join->on('rosters.player_id', '=', 'players.id')
                    ->whereIn('club_team_season_id', $clubTeamSeasonIds);
            })
            ->get()
            ->keyBy('id');

        $playerOrder    = [];
        $groupedPlayers = [];

        foreach ($players as $player)
        {
            $playerOrder[] = $player->id;

            foreach ($player->positions as $positions)
            {
                $groupedPlayers[$positions->position_name][] = $player;
            }
        }

        $formations = Formation::all()
            ->keyBy('id');

        $groupedFormations = [];

        foreach ($formations as $formation)
        {
            $dashed = implode('-', str_split($formation->name, 1));

            $groupedFormations[$formation->players][] = [
                'id'   => $formation->id,
                'name' => $dashed,
            ];
        }

        $events = Event::all()
            ->keyBy('id');

        return view('games.live', [
            'result'            => $result,
            'resultEvents'      => $resultEvents,
            'groupedFormations' => $groupedFormations,
            'formations'        => $formations,
            'order'             => $playerOrder,
            'players'           => $players,
            'groupedPlayers'    => $groupedPlayers,
            'events'            => $events,
        ]);
    }
}