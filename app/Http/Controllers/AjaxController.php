<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Position;
use App\Models\Result;
use App\Models\ResultEvent;
use App\Models\Player;
use App\Enums\Event as EnumEvent;
use App\Models\PlayerPosition;
use App\Models\Roster;

class AjaxController extends Controller
{
    /**
     * gameStart 
     * 
     * @param Request $request 
     * @return json
     */
    public function gameStart(Request $request)
    {
        $validated = $request->validate([
            'resultId'    => 'required|integer',
            'starters'    => 'required',
            'formationId' => 'required|integer',
        ]);

        $positionLkup = Position::get()
            ->pluck('id', 'position')
            ->toArray();

        foreach ($request->starters as $playerId => $positionName)
        {
            $event = new ResultEvent;

            $event->result_id  = $request->resultId;
            $event->player_id  = $playerId;
            $event->time       = '00:00:00';
            $event->event_id   = EnumEvent::start;
            $event->additional = $positionName;
            $event->created_user_id = Auth()->user()->id;
            $event->updated_user_id = Auth()->user()->id;

            $event->save();
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'result_id' => $request->resultId,
            ],
        ], 200);
    }

    /**
     * saveEvent 
     * 
     * @param Request $request 
     * @return json
     */
    public function saveEvent(Request $request)
    {
        $validated = $request->validate([
            'result_id'   => 'required|integer',
            'player_id'   => 'sometimes|integer',
            'time'        => 'required|regex:/^\d?\d?\d:\d\d$/',
            'event_id'    => 'required|integer',
            'pk_fk'       => 'nullable|in:penalty,free_kick',
            'additional'  => 'nullable',
            'xg'          => 'nullable|integer',
            'notes'       => 'nullable|min:3|max:255',
        ]);

        $event = new ResultEvent;

        $eventId = $request->event_id;

        if ($request->has('pk_fk'))
        {
            if ($request->pk_fk == 'penalty')
            {
                $eventId = $request->event_id == EnumEvent::goal->value ? EnumEvent::penalty_goal->value
                    : ($request->event_id == EnumEvent::shot_on_target->value ? EnumEvent::penalty_on_target->value : EnumEvent::penalty_off_target->value);
            }
            if ($request->pk_fk == 'free_kick')
            {
                $eventId = $request->event_id == EnumEvent::goal->value ? EnumEvent::free_kick_goal->value
                    : ($request->event_id == EnumEvent::shot_on_target->value ? EnumEvent::free_kick_on_target->value : EnumEvent::free_kick_off_target->value);
            }
        }

        if ($request->has('additional'))
        {
            $event->additional = $request->additional;
        }

        if ($request->has('xg'))
        {
            $event->xg = $request->xg;
        }

        if ($request->has('player_id'))
        {
            $event->player_id  = $request->player_id;
        }

        $event->result_id  = $request->result_id;
        $event->time       = $request->time;
        $event->event_id   = $eventId;
        $event->created_user_id = Auth()->user()->id;
        $event->updated_user_id = Auth()->user()->id;

        $event->save();

        $response = $event->toArray();

        $response['event_name'] = EnumEvent::from($eventId)->name;

        $response['player_name'] = Player::find($event->player_id)->name;

        return response()->json([
            'success' => true,
            'data'    => $response,
        ], 200);
    }

    /**
     * gameEnd 
     * 
     * @param Request $request 
     * @return json
     */
    public function gameEnd(Request $request)
    {
        $validated = $request->validate([
            'resultId'  => 'required|integer',
            'time'      => 'required|regex:/^\d?\d?\d:\d\d$/',
            'homeScore' => 'required|integer',
            'awayScore' => 'required|integer',
        ]);

        // Save fulltime event
        $event = new ResultEvent;

        $event->result_id       = $request->resultId;
        $event->time            = $request->time;
        $event->event_id        = EnumEvent::fulltime->value;
        $event->created_user_id = Auth()->user()->id;
        $event->updated_user_id = Auth()->user()->id;

        $event->save();

        // Update result status to done and set final score
        $existingResult = Result::find($request->resultId);

        $existingResult->home_team_score = $request->homeScore;
        $existingResult->away_team_score = $request->awayScore;
        $existingResult->status          = 'D';

        $existingResult->save();

        return response()->json([
            'success' => true,
            'data'    => [
                'result'   => $existingResult->toArray(),
                'redirect' => route('home'),
            ],
        ], 200);
    }

    /**
     * savePlayerPosition
     * 
     * @param Request $request 
     * @return json
     */
    public function savePlayerPosition(Request $request)
    {
        $validated = $request->validate([
            'player_id'   => 'required|integer',
            'position_id' => 'required|integer',
        ]);

        // Save the new player position
        $playerPosition = new PlayerPosition;

        $playerPosition->player_id       = $request->player_id;
        $playerPosition->position_id     = $request->position_id;
        $playerPosition->created_user_id = Auth()->user()->id;
        $playerPosition->updated_user_id = Auth()->user()->id;

        $playerPosition->save();

        // Get all positions for this player
        $positions = PlayerPosition::where('player_id', $request->player_id)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $positions->toArray(),
        ], 200);
    }

    /**
     * saveRoster
     * 
     * @param Request $request 
     * @return json
     */
    public function saveRoster(Request $request)
    {
        $validated = $request->validate([
            'club_team_season_id' => 'required|integer',
            'player_id'           => 'required|integer',
            'number'              => 'nullable|integer',
        ]);

        // Save the new player position
        $roster = new Roster;

        $roster->club_team_season_id = $request->club_team_season_id;
        $roster->player_id           = $request->player_id;
        $roster->number              = $request->number;
        $roster->created_user_id     = Auth()->user()->id;
        $roster->updated_user_id     = Auth()->user()->id;

        $roster->save();

        return response()->json([
            'success' => true,
            'data'    => $roster->toArray(),
        ], 200);
    }
}
