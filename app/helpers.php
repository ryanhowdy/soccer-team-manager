<?php

use App\Models\ResultEvent;
use App\Enums\Event;
use App\Enums\ResultStatus;

/*
 * Helpers
 *
 * Some usefule global helper/utility functions
 */

if (!function_exists('clubHasManagedTeam'))
{
    /**
     * Does this club have at least one team we manage? Decides whether it lists
     * on Manage -> Teams.
     *
     * "managed" is a column on club_teams rather than on clubs, so a club counts
     * as ours as soon as one of its teams is managed.
     *
     * @param  App\Models\Club $club  with teams loaded
     * @return bool
     */
    function clubHasManagedTeam($club): bool
    {
        return $club->teams->where('managed', 1)->isNotEmpty();
    }
}

if (!function_exists('clubListsAsOpponent'))
{
    /**
     * Does this club belong on Manage -> Opponents?
     *
     * Deliberately NOT the inverse of clubHasManagedTeam(): a club with a mix of
     * managed and unmanaged teams shows on both pages, so those unmanaged teams
     * stay reachable from Opponents instead of being hidden behind the managed
     * half. The two pages therefore overlap rather than partition.
     *
     * @param  App\Models\Club $club  with teams loaded
     * @return bool
     */
    function clubListsAsOpponent($club): bool
    {
        // A club with no teams yet — one just created from the Add Club modal —
        // still belongs here, or it would vanish the moment it was added.
        if ($club->teams->isEmpty())
        {
            return true;
        }

        return $club->teams->where('managed', '!=', 1)->isNotEmpty();
    }
}

if (!function_exists('addOrdinalNumberSuffix'))
{
    function addOrdinalNumberSuffix($num)
    {
        if (!in_array(($num % 100), array(11,12,13)))
        {
            switch ($num % 10)
            {
                // Handle 1st, 2nd, 3rd
                case 1:  return $num . 'st';
                case 2:  return $num . 'nd';
                case 3:  return $num . 'rd';
            }
        }

        return $num . 'th';
    }
}

if (!function_exists('eventTimeToSeconds'))
{
    /**
     * eventTimeToSeconds 
     * 
     * Given an event time (which is stored as (H:i:s)) but we treat it as
     * minutes, seconds and ignore the last part) converts it to just seconds.
     *
     * @param string $eventTime 
     * @return int
     */
    function eventTimeToSeconds($eventTime)
    {
        $seconds = 0;

        if ($eventTime)
        {
            $timeParts = explode(':', $eventTime);

            // minutes
            $seconds += ($timeParts[0] * 60);

            // seconds
            $seconds += $timeParts[1];
        }

        return $seconds;
    }
}

if (!function_exists('secondsToMinutes'))
{
    /**
     * secondsToMinutes
     * 
     * Will display seconds in whole minutes, rounded.
     *
     * @param int $seconds
     * @return int
     */
    function secondsToMinutes($time)
    {
        return round($time / 60);
    }
}

if (!function_exists('createGoogleMapsUrlFromAddress'))
{
    /**
     * createGoogleMapsUrlFromAddress 
     * 
     * @param string  $address 
     * @return string
     */
    function createGoogleMapsUrlFromAddress($address)
    {
        $url = $address;

        // Fix percent sign - needs to be done first
        $url = str_replace("%", "%25", $url);

        // Fix spaces
        $url = str_replace(" ", "%20", $url);
        // Fix double quotes
        $url = str_replace('"', "%22", $url);
        $url = str_replace("<", "%3C", $url);
        $url = str_replace(">", "%3E", $url);
        $url = str_replace("#", "%23", $url);
        $url = str_replace("|", "%7C", $url);

        return 'https://www.google.com/maps/dir/?api=1&destination='.$url;
    }
}

if (!function_exists('dedupeResultEvents'))
{
    /**
     * dedupeResultEvents 
     * 
     * @param Illuminate\Database\Eloquent\Collection $events 
     * @return Illuminate\Database\Eloquent\Collection
     */
    function dedupeResultEvents(Illuminate\Database\Eloquent\Collection $events)
    {
        $check = [];

        $goalValues = Event::getGoalValues();

        foreach ($events as $eventKey => $e)
        {
            $cTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $e->created_at->format('Y-m-d') . ' 00:' . substr($e->time, 0, 5));
            $time  = $cTime->floorMinute(2)->format('i');

            $checkKey = $e->against . '_' . $e->event_name . '_' . $time;

            // check if any similar events to this already exist

            // check same team/event type/time
            if (isset($check[$checkKey]))
            {
                // possible duplicate - loop through all the possible matches
                foreach ($check[$checkKey] as $otherKey => $otherEvent)
                {
                    // not a dupe - entered by the same user
                    if ($e->created_user_id == $otherEvent->created_user_id)
                    {
                        continue;
                    }

                    // it's a duplicate - delete on of them
                    $keyToDelete = getLowestPriorityEvent($eventKey, $e, $otherKey, $otherEvent);

                    $events->forget($keyToDelete);
                    $check[$checkKey][$eventKey] = $e;
                    continue 2;
                }
            }

            // Save the event for later
            if (!isset($check[$checkKey]))
            {
                $check[$checkKey] = [];
            }

            $check[$checkKey][$eventKey] = $e;
        }

        return $events;
    }
}


if (!function_exists('getLowestPriorityEvent'))
{
    /**
     * getLowestPriorityEvent 
     * 
     * @param int $key1 
     * @param ResultEvent $event1 
     * @param int $key2 
     * @param ResultEvent $event2 
     * @return int
     */
    function getLowestPriorityEvent(int $key1, ResultEvent $event1, int $key2, ResultEvent $event2)
    {
        // admin
        if ($event2->userRolesManagedPlayers->hasRole('admin') && !$event1->userRolesManagedPlayers->hasRole('admin'))
        {
            return $key1;
        }
        // manager
        if ($event2->userRolesManagedPlayers->hasRole('manager') && !$event1->userRolesManagedPlayers->hasRole('manager'))
        {
            return $key1;
        }

        if ($event2->userRolesManagedPlayers->managedPlayers->count())
        {
            foreach ($event2->userRolesManagedPlayers->managedPlayers as $p)
            {
                // this event is for a managed player of the person who entered it
                if ($p->player_id == $event2->player_id)
                {
                    return $key1;
                }
            }
        }

        // default to the deleting the 2nd one
        return $key2;
    }
}

if (!function_exists('getLineupForEventTime'))
{
    function getLineupForEventTime($time, $lineups)
    {
        $lineup = [];

        $timeSecs = eventTimeToSeconds($time);

        foreach ($lineups as $playerId => $spans)
        {
            foreach ($spans as $s)
            {
                $startSecs = eventTimeToSeconds($s['start']);
                $endSecs   = eventTimeToSeconds($s['end']);

                if ($timeSecs >= $startSecs && (is_null($s['end']) || $timeSecs <= $endSecs)) {
                    // found a player who was on the field when this event occurred, 
                    // save and move on to the next player
                    $lineup[] = $playerId;
                    continue 2;
                }
            }
        }

        sort($lineup);

        return $lineup;
    }
}

if (!function_exists('resolveSeasonFilter'))
{
    /**
     * Resolve which season a season-filtered page should show.
     *
     * Precedence:
     *   1. ?filter-seasons on the request — also remembered for later pages
     *   2. the season last picked, from the session
     *   3. the most recent season
     *
     * An empty ?filter-seasons means "all seasons" and returns null. Pages that
     * can't render every season at once (a roster belongs to one team-season)
     * pass $allowAll = false and fall back to the most recent season instead.
     *
     * @param  Illuminate\Http\Request $request
     * @param  Illuminate\Support\Collection $seasons  newest-first, keyed by id
     * @param  bool $allowAll
     * @return App\Models\Season|null
     */
    function resolveSeasonFilter($request, $seasons, bool $allowAll = true)
    {
        $latest = $seasons->first();

        if ($request->has('filter-seasons'))
        {
            $seasonId = $request->input('filter-seasons');

            if ($seasonId === '' || is_null($seasonId))
            {
                session(['selected_season_id' => 'all']);

                return $allowAll ? null : $latest;
            }

            $season = $seasons[$seasonId] ?? null;

            if ($season)
            {
                session(['selected_season_id' => $season->id]);

                return $season;
            }

            return $latest;
        }

        $remembered = session('selected_season_id');

        if ($remembered === 'all')
        {
            return $allowAll ? null : $latest;
        }

        return $seasons[$remembered] ?? $latest;
    }
}
