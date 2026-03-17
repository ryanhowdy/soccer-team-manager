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
use App\Models\PenaltyShootout;
use App\Enums\Event as EnumEvent;
use Carbon\Carbon;

class LiveGameController extends Controller
{
    /**
     * Displays the live game view
     *
     * @param Request $request 
     * @param int $id 
     * @return Illuminate\View\View
     */
    public function index(Request $request, int $id)
    {
        return view('games.live.index', [
            'id' => $id,
        ]);
    }

    /**
     * all 
     * 
     * @param Request $request 
     * @param int $id 
     * @return Illuminate\View\View
     */
    public function all(Request $request, int $id)
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

        // Compute live state for resuming across browsers
        $liveState = $this->computeLiveState($result);

        return view('games.live.all', [
            'result'            => $result,
            'resultEvents'      => $resultEvents,
            'groupedFormations' => $groupedFormations,
            'formations'        => $formations,
            'order'             => $playerOrder,
            'players'           => $players,
            'groupedPlayers'    => $groupedPlayers,
            'events'            => $events,
            'liveState'         => $liveState,
        ]);
    }

    /**
     * possession
     * 
     * @param Request $request 
     * @param int $id 
     * @return Illuminate\View\View
     */
    public function possession(Request $request, int $id)
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

        // Compute live state for resuming across browsers
        $liveState = $this->computeLiveState($result);

        return view('games.live.possession', [
            'result'    => $result,
            'liveState' => $liveState,
        ]);
    }

    /**
     * pk
     * 
     * @param Request $request 
     * @param int $id 
     * @return Illuminate\View\View
     */
    public function pk(Request $request, int $id)
    {
        // Get the result info
        $result = Result::with('competition')
            ->with('location')
            ->with('homeTeam.club')
            ->with('awayTeam.club')
            ->find($id);

        // if the result isn't done already, just redirect to live
        if ($result->status != 'D')
        {
            return redirect()->route('games.live', ['id' => $id]);
        }

        // get any pk info we already have for this game
        $shootout = PenaltyShootout::latest()
            ->with('penalties')
            ->where('result_id', $result->id)
            ->first();

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

        $rounds = 5;
        if (!is_null($shootout) && $shootout->penalties->count() > 10)
        {
            $rounds = ceil($shootout->penalties->count() / 2);
        }

        return view('games.live.pk', [
            'result'       => $result,
            'existingData' => $shootout,
            'players'      => $players,
            'rounds'       => $rounds,
            'key'          => 0,
        ]);
    }

    /**
     * computeLiveState
     *
     * Compute the current live game state from DB columns and events.
     *
     * @param Result $result
     * @return array
     */
    private function computeLiveState(Result $result): array
    {
        $liveState = [
            'started'     => false,
            'period'      => null,
            'timerSeconds' => null,
            'timerRunning' => false,
            'formationId' => null,
            'starters'    => [],
        ];

        // Not started yet
        if ($result->live_period === null)
        {
            return $liveState;
        }

        $liveState['started'] = true;
        $liveState['period']  = $result->live_period;

        // Calculate current elapsed seconds
        $offset = $result->live_timer_offset ?? 0;

        if ($result->live_timer_started_at !== null)
        {
            $liveState['timerRunning'] = true;
            $liveState['timerSeconds'] = $offset + (int) $result->live_timer_started_at->diffInSeconds(Carbon::now());
        }
        else
        {
            $liveState['timerSeconds'] = $offset;
        }

        // Formation
        $liveState['formationId'] = $result->formation_id;

        // Compute current starters from events
        $lineupEvents = ResultEvent::where('result_id', $result->id)
            ->whereIn('event_id', [
                EnumEvent::start->value,
                EnumEvent::sub_in->value,
                EnumEvent::sub_out->value,
            ])
            ->orderBy('id')
            ->get();

        $starters = [];

        foreach ($lineupEvents as $event)
        {
            if ($event->event_id == EnumEvent::start->value || $event->event_id == EnumEvent::sub_in->value)
            {
                $starters[$event->player_id] = $event->additional;
            }
            elseif ($event->event_id == EnumEvent::sub_out->value)
            {
                unset($starters[$event->player_id]);
            }
        }

        $liveState['starters'] = $starters;

        return $liveState;
    }
}
