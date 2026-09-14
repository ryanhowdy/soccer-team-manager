<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Result;
use App\Models\ResultEvent;
use App\Enums\Event as EnumEvent;

class ResultEventController extends Controller
{
    // How long one event keeps showing on the momentum chart, in minutes.
    private const MOMENTUM_WINDOW = 5;

    // Stands in for an unrated shot.  The median of the xG actually recorded,
    // so leaving the rating off neither wipes the chance out nor inflates it.
    private const MOMENTUM_DEFAULT_XG = 3;

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
     * update
     *
     * @param Request $request
     * @param Result $result
     * @param ResultEvent $resultEvent
     * @return json
     */
    public function update(Request $request, Result $result, ResultEvent $resultEvent)
    {
        if (Auth()->user()->cannot('edit things'))
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'player_id'   => 'sometimes|exists:players,id',
            'against'     => 'sometimes|integer',
            'time'        => 'required|regex:/^\d?\d?\d:\d\d$/',
            'event_id'    => 'required|integer',
            'additional'  => 'nullable',
            'xg'          => 'nullable|integer',
            'notes'       => 'nullable|min:3|max:255',
        ]);

        $resultEvent->time     = $request->time;
        $resultEvent->event_id = $request->event_id;
        $resultEvent->updated_user_id = Auth()->user()->id;

        $resultEvent->player_id  = $request->filled('player_id') ? $request->player_id : null;
        $resultEvent->against    = $request->filled('against') ? $request->against : 0;
        $resultEvent->additional = $request->filled('additional') ? $request->additional : null;
        $resultEvent->xg         = $request->filled('xg') ? $request->xg : null;
        $resultEvent->notes      = $request->filled('notes') ? $request->notes : null;

        $resultEvent->save();

        return response()->json([
            'success' => true,
            'data'    => $resultEvent->toArray(),
        ], 200);
    }

    /**
     * destroy
     *
     * @param Request $request
     * @param Result $result
     * @param ResultEvent $resultEvent
     * @return json
     */
    public function destroy(Request $request, Result $result, ResultEvent $resultEvent)
    {
        if (Auth()->user()->cannot('edit things'))
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $resultEvent->delete();

        return response()->json([
            'success' => true,
        ], 200);
    }

    /**
     * bulkDestroy
     *
     * Admin-only bulk deletion of timeline events.
     *
     * @param Request $request
     * @param Result $result
     * @return json
     */
    public function bulkDestroy(Request $request, Result $result)
    {
        if (Auth()->user()->cannot('edit things'))
        {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'event_ids'   => 'required|array|min:1',
            'event_ids.*' => 'integer|exists:result_events,id',
        ]);

        $deleted = ResultEvent::where('result_id', $result->id)
            ->whereIn('id', $validated['event_ids'])
            ->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
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
     * Attacking threat over the game, as a single signed series: positive is
     * the home side on top, negative the away side.
     *
     * Each event is worth a fixed amount plus its xG where it has one, and
     * keeps influencing the chart for MOMENTUM_WINDOW minutes while fading
     * out, which is what turns a list of events into a curve.
     *
     * @param Result $result
     * @return json
     */
    public function getMomentum(Result $result, Request $request)
    {
        $resultEvents = ResultEvent::where('result_id', $result->id)
            ->orderBy('time')
            ->orderBy('id')
            ->get();

        $goodGuys = $result->homeTeam->managed ? 'home' : 'away';
        $badGuys  = $goodGuys == 'home'        ? 'away' : 'home';

        // Raw value dropped at the minute it happened, before any fading.
        $raw     = ['home' => [], 'away' => []];
        $markers = [];
        $last    = 0;

        foreach ($resultEvents as $e)
        {
            $minute = (int) floor(eventTimeToSeconds($e->time) / 60);

            $last = max($last, $minute);

            // Goals and cards get drawn on the line.  These sit on the side they
            // happened to, which is not always the side they hand momentum to -
            // a booking helps the other team but belongs to the booked player.
            $marker = $this->momentumMarker($e);

            if ($marker)
            {
                $markers[] = [
                    'minute' => $minute,
                    'type'   => $marker,
                    'side'   => $e->against ? $badGuys : $goodGuys,
                    // Opponent events rarely name a player, so the view falls
                    // back to the team when this is null.
                    'player' => $e->player ? $e->player->name : null,
                ];
            }

            $scored = $this->momentumValue($e, $goodGuys, $badGuys);

            if (is_null($scored))
            {
                continue;
            }

            [$side, $value] = $scored;

            $raw[$side][$minute] = ($raw[$side][$minute] ?? 0) + $value;
        }

        // Fade each minute's events out over the window that follows, then take
        // the difference between the sides.  One number per minute, so the x
        // axis is real time whether or not anything happened.
        $series = [];
        $peak   = 0;

        for ($minute = 0; $minute <= $last; $minute++)
        {
            $value = $this->momentumAt($raw['home'], $minute) - $this->momentumAt($raw['away'], $minute);

            $series[$minute] = $value;
            $peak            = max($peak, abs($value));
        }

        // Scale to -1..1 off the biggest swing in this game.  A game with no
        // momentum events at all leaves peak at 0 - flat line, no division.
        foreach ($series as $minute => $value)
        {
            $series[$minute] = $peak > 0 ? round($value / $peak, 3) : 0;
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'minutes' => array_keys($series),
                'values'  => array_values($series),
                'markers' => $markers,
                'teams'   => [
                    'home' => $result->homeTeam->short_name,
                    'away' => $result->awayTeam->short_name,
                ],
            ],
        ], 200);
    }

    /**
     * momentumValue
     *
     * What one event is worth, and to which side.  Null for events that say
     * nothing about momentum - substitutions, the whistle, possession flags.
     *
     * Weights are deliberately flat per event type plus xG on the shooting
     * ones: the swing from a chance is carried by its xG, which is why a shot
     * on and off target are worth the same base.
     *
     * @param ResultEvent $event
     * @param string $goodGuys
     * @param string $badGuys
     * @return array|null  [side, value]
     */
    private function momentumValue(ResultEvent $event, string $goodGuys, string $badGuys): ?array
    {
        $side  = $event->against ? $badGuys : $goodGuys;
        $other = $side === 'home' ? 'away' : 'home';

        // An unrated shot should not outweigh a rated one, so it takes the
        // middle of the scale rather than sitting near the top of it.
        $xg = is_null($event->xg) ? self::MOMENTUM_DEFAULT_XG : $event->xg;

        if (in_array($event->event_id, EnumEvent::getGoalValues()))
        {
            return [$side, 10 + $xg];
        }

        if (in_array($event->event_id, EnumEvent::getShotValues()))
        {
            return [$side, 4 + $xg];
        }

        // A save sits on our own keeper, but it is the other side's shot on
        // target - and it is how nearly every one of theirs gets recorded, so
        // leaving it out hides most of the opposition's attacking play.
        if ($event->event_id == EnumEvent::save->value)
        {
            return [$other, 4 + $xg];
        }

        if ($event->event_id == EnumEvent::corner_kick->value)
        {
            return [$side, 3];
        }

        if ($event->event_id == EnumEvent::tackle_won->value)
        {
            return [$side, 1];
        }

        if ($event->event_id == EnumEvent::tackle_lost->value)
        {
            return [$other, 2];
        }

        // Won a foul, or got in behind and was flagged - both say you were the
        // side doing something.
        if (in_array($event->event_id, [EnumEvent::fouled->value, EnumEvent::offsides->value]))
        {
            return [$side, 1];
        }

        if (in_array($event->event_id, [EnumEvent::foul->value, EnumEvent::yellow_card->value]))
        {
            return [$other, 1];
        }

        if ($event->event_id == EnumEvent::red_card->value)
        {
            return [$other, 10];
        }

        return null;
    }

    /**
     * momentumMarker
     *
     * The moments worth drawing on the line rather than just moving it.
     *
     * @param ResultEvent $event
     * @return string|null
     */
    private function momentumMarker(ResultEvent $event): ?string
    {
        if (in_array($event->event_id, EnumEvent::getGoalValues()))
        {
            return 'goal';
        }

        if ($event->event_id == EnumEvent::yellow_card->value)
        {
            return 'yellow';
        }

        if ($event->event_id == EnumEvent::red_card->value)
        {
            return 'red';
        }

        return null;
    }

    /**
     * momentumAt
     *
     * One side's standing at a given minute: everything it did in the window
     * up to now, each fading linearly with age so a chance counts for full at
     * the moment it happens and nothing once the window has passed.
     *
     * @param array $raw  value keyed by the minute it happened
     * @param int $minute
     * @return float
     */
    private function momentumAt(array $raw, int $minute): float
    {
        $total = 0;

        for ($age = 0; $age < self::MOMENTUM_WINDOW; $age++)
        {
            $was = $minute - $age;

            if ($was < 0 || !isset($raw[$was]))
            {
                continue;
            }

            $total += $raw[$was] * (1 - ($age / self::MOMENTUM_WINDOW));
        }

        return $total;
    }
}
