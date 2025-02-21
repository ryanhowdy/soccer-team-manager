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
use App\Enums\Event as EnumEvent;

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

        // if the result already is marked as done/cancelled, redirect to the game details
        if (in_array($result->status, ['D','C']))
        {
            return redirect()->route('games.show', ['id' => $id]);
        }

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
            ->where('event_id', '!=', EnumEvent::start->value)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        // get the players for this team
        $guestPlayers = Player::select('players.*', 'roster_guests.number')
            ->with('positions')
            ->join('roster_guests', function (JoinClause $join) use ($result) {
                $join->on('roster_guests.player_id', '=', 'players.id')
                    ->where('result_id', $result->id);
            })
            ->get();

        $currentPlayers = Player::select('players.*', 'rosters.number')
            ->with('positions')
            ->join('rosters', function (JoinClause $join) use ($result) {
                $join->on('rosters.player_id', '=', 'players.id')
                    ->where('club_team_season_id', $result->club_team_season_id);
            })
            ->get();

        $players = $currentPlayers->merge($guestPlayers)
            ->sortBy('name')
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

        if ($formations->isEmpty())
        {
            return redirect()->route('formations.index')->withErrors(['You must create at least 1 formation.']);
        }

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
