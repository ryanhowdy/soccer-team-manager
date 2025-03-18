<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Result;
use App\Models\ResultEvent;
use App\Enums\Event as EnumEvent;

class ResultEventController extends Controller
{
    /**
     * store
     * 
     * @param Request $request 
     * @return json
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'result_id'   => 'required|exists:results,id',
            'player_id'   => 'sometimes|exists:players,id',
            'against'     => 'sometimes|integer',
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

        if ($request->filled('additional'))
        {
            $event->additional = $request->additional;
        }
        if ($request->filled('xg'))
        {
            $event->xg = $request->xg;
        }
        if ($request->filled('player_id'))
        {
            $event->player_id = $request->player_id;
        }
        if ($request->filled('against'))
        {
            $event->against = $request->against;
        }
        if ($request->filled('notes'))
        {
            $event->notes  = $request->notes;
        }

        $event->result_id  = $request->result_id;
        $event->time       = $request->time;
        $event->event_id   = $eventId;
        $event->created_user_id = Auth()->user()->id;
        $event->updated_user_id = Auth()->user()->id;

        $event->save();

        return response()->json([
            'success' => true,
            'data'    => $event->toArray(),
        ], 200);
    }

    /**
     * getPossession
     * 
     * @param Result $result
     * @return json
     */
    public function getPossession(Result $result, Request $request)
    {
        // Get all the events for this game
        $resultEvents = ResultEvent::where('result_id', $result->id)
            ->whereIn('event_id', [EnumEvent::gain_possession, EnumEvent::lose_possession])
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        $goodGuys = $result->homeTeam->managed ? 'home' : 'away';
        $badGuys  = $goodGuys == 'home'        ? 'away' : 'home';

        $possession = [
            'home' => [
                'seconds' => 0,
                'spans'   => [],
            ],
            'away' => [
                'seconds' => 0,
                'spans'   => [],
            ],
        ];

        foreach($resultEvents as $e)
        {
            if ($e->event_id == EnumEvent::gain_possession->value)
            {
                // Start new time span for the good guys
                $possession[$goodGuys]['spans'][] = [
                    'start' => $e->time,
                    'end'   => null,
                ];

                // Close the last time span for bad guys and add up the time in seconds
                foreach($possession[$badGuys]['spans'] as $i => $span)
                {
                    if ($span['end'] === null)
                    {
                        $possession[$badGuys]['spans'][$i]['end'] = $e->time;

                        $start = eventTimeToSeconds($span['start']);
                        $end   = eventTimeToSeconds($e->time);

                        $secs = $end - $start;

                        $possession[$badGuys]['seconds'] += $secs;
                    }
                }
            }
            if ($e->event_id == EnumEvent::lose_possession->value)
            {
                // Start new time span for the bad guys
                $possession[$badGuys]['spans'][] = [
                    'start' => $e->time,
                    'end'   => null,
                ];

                // Close the last time span for good guys and add up the time in seconds
                foreach($possession[$goodGuys]['spans'] as $i => $span)
                {
                    if ($span['end'] === null)
                    {
                        $possession[$goodGuys]['spans'][$i]['end'] = $e->time;

                        $start = eventTimeToSeconds($span['start']);
                        $end   = eventTimeToSeconds($e->time);

                        $secs = $end - $start;

                        $possession[$goodGuys]['seconds'] += $secs;
                    }
                }
            }
        }

        foreach($possession as $key => $data)
        {
            // End the time range for everyone who was in the game at fulltime
            foreach($possession[$key]['spans'] as $i => $span)
            {
                if ($span['end'] === null)
                {
                    $possession[$key]['spans'][$i]['end'] = $request->time;

                    $start = eventTimeToSeconds($span['start']);
                    $end   = eventTimeToSeconds($request->time);

                    $secs = $end - $start;

                    $possession[$key]['seconds'] += $secs;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $possession,
        ], 200);
    }

    /**
     * getMomentum
     * 
     * @param Result $result
     * @return json
     */
    public function getMomentum(Result $result, Request $request)
    {
        // Get all the events for this game
        $resultEvents = ResultEvent::where('result_id', $result->id)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        $goodGuys = $result->homeTeam->managed ? 'home' : 'away';
        $badGuys  = $goodGuys == 'home'        ? 'away' : 'home';

        $momentum = [
            'home' => [],
            'away' => [],
        ];

        $max = 0;

        foreach($resultEvents as $e)
        {
            $homeAway = $e->against == 1 ? $badGuys : $goodGuys;

            // put each time event into 5 min sections (ie 0-4, 5-9, 10-14, etc)
            $time = floor(substr($e->time, 0, 2) / 5) * 5;

            if (!isset($momentum['home'][$time]))
            {
                $momentum['home'][$time] = [
                    'points' => 0,
                    'total'  => 0,
                    'event'  => '',
                ];
            }
            if (!isset($momentum['away'][$time]))
            {
                $momentum['away'][$time] = [
                    'points' => 0,
                    'total'  => 0,
                    'event'  => '',
                ];
            }

            // Goal (10)
            if (in_array($e->event_id, EnumEvent::getGoalValues()))
            {
                $momentum[$homeAway][$time]['points'] += 10;
                $momentum[$homeAway][$time]['event'] = 'goal';
            }
            // Shot on/off Target (xg)
            if (in_array($e->event_id, EnumEvent::getShotValues()))
            {
                $pts = is_null($e->xg) ? 5 : $e->xg;

                $momentum[$homeAway][$time]['points'] += $pts;
            }
            // Foul (-2)
            if ($e->event_id == EnumEvent::foul->value)
            {
                $momentum[$badGuys][$time]['points'] += 2;
            }
            // Fouled (2)
            if ($e->event_id == EnumEvent::fouled->value)
            {
                $momentum[$goodGuys][$time]['points'] += 2;
            }

            if ($momentum['home'][$time]['points'] > $max)
            {
                $max = $momentum['home'][$time]['points'];
            }
            if ($momentum['away'][$time]['points'] > $max)
            {
                $max = $momentum['away'][$time]['points'];
            }
        }

        foreach ($momentum['home'] as $timeSpan => $data)
        {
            $hPoints = $data['points'] / $max;
            $aPoints = $momentum['away'][$timeSpan]['points'] / $max;

            if ($hPoints > $aPoints)
            {
                $momentum['home'][$timeSpan]['total'] = round($hPoints, 1);
            }
            if ($aPoints > $hPoints)
            {
                $momentum['away'][$timeSpan]['total'] = round($aPoints, 1);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $momentum,
        ], 200);
    }
}
