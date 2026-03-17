<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Result;
use App\Models\ResultEvent;
use App\Models\Player;
use App\Enums\Event as EnumEvent;
use Carbon\Carbon;

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

    /**
     * updateLiveState
     *
     * Update the live timer state for a game in progress.
     *
     * @param Result  $result
     * @param Request $request
     * @return json
     */
    /**
     * getLiveState
     *
     * Get the current live game state for polling/sync.
     *
     * @param Result $result
     * @return json
     */
    public function getLiveState(Result $result)
    {
        $liveState = [
            'started'      => false,
            'period'       => null,
            'timerSeconds' => null,
            'timerRunning' => false,
        ];

        if ($result->live_period !== null)
        {
            $liveState['started'] = true;
            $liveState['period']  = $result->live_period;

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
        }

        // Include all result events (excluding start events) for tab sync
        $resultEvents = ResultEvent::where('result_id', $result->id)
            ->where('event_id', '!=', EnumEvent::start->value)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        $events = [];

        foreach ($resultEvents as $event)
        {
            $eventData = $event->toArray();
            $eventData['event_name'] = EnumEvent::from($event->event_id)->name;
            $eventData['player_name'] = $event->player_id ? Player::find($event->player_id)->name : '';

            $events[] = $eventData;
        }

        $liveState['resultEvents'] = $events;

        return response()->json([
            'success' => true,
            'data'    => $liveState,
        ], 200);
    }

    public function updateLiveState(Result $result, Request $request)
    {
        $validated = $request->validate([
            'live_period'       => 'nullable|in:1,half,2',
            'live_timer_offset' => 'nullable|integer|min:0',
            'timer_running'     => 'required|boolean',
        ]);

        $result->live_period       = $request->live_period;
        $result->live_timer_offset = $request->live_timer_offset;

        if ($request->timer_running)
        {
            $result->live_timer_started_at = Carbon::now();
        }
        else
        {
            $result->live_timer_started_at = null;
        }

        $result->save();

        return response()->json([
            'success' => true,
            'data'    => $result->toArray(),
        ], 200);
    }
}
