<?php

namespace App\Services;

use App\Enums\Event;

class KeeperStats
{
    /**
     * Goalkeeping, keyed by player name. Works on any set of events, from
     * one game (games/show) to a whole season (stats/team).
     *
     * Saves are recorded on the keeper who made them, so those are never in
     * doubt.  Goals conceded are not recorded on anybody, so they have to be
     * charged to whoever was in goal at the time.  That comes from the
     * position in `additional` on a start or sub_in, which only became
     * reliable in the 2024 Fall season - so a game where nobody is marked in
     * goal contributes saves and nothing else, and the keeper's save
     * percentage and goals prevented say how many games they are built from.
     *
     * @param  \Illuminate\Support\Collection $events
     * @param  array $goalEvents
     * @return array
     */
    public function calculate($events, array $goalEvents)
    {
        $spans   = $this->keeperSpans($events);
        $keepers = [];

        $defaults = [
            'player'  => null,
            'saves'   => 0,
            'games'   => [],
            // Only what happened in a game we can pin on a keeper.
            'known'   => [],
            'shots'   => 0,
            'ga'      => 0,
            'xgFaced' => 0,
        ];

        foreach ($events as $event)
        {
            $known = isset($spans[$event->result_id]);

            // A save, recorded on our keeper rather than against us.
            if (!$event->against && $event->event_id == Event::save->value && $event->player_id)
            {
                $name = $event->player_name;

                $keepers[$name] ??= $defaults;
                $keepers[$name]['player'] = $event->player;
                $keepers[$name]['saves']++;
                $keepers[$name]['games'][$event->result_id] = true;

                if ($known)
                {
                    $keepers[$name]['known'][$event->result_id] = true;
                    $keepers[$name]['shots']++;
                    $keepers[$name]['xgFaced'] += $event->xg !== null ? $event->xg / 10 : 0;
                }
            }

            // A goal conceded, charged to whoever was in goal when it went in.
            if ($event->against && in_array($event->event_id, $goalEvents) && $known)
            {
                $name = $this->keeperAt($spans[$event->result_id], $event->time);

                if (!$name)
                {
                    continue;
                }

                $keepers[$name] ??= $defaults;
                $keepers[$name]['games'][$event->result_id] = true;
                $keepers[$name]['known'][$event->result_id] = true;
                $keepers[$name]['ga']++;
                $keepers[$name]['shots']++;
                $keepers[$name]['xgFaced'] += $event->xg !== null ? $event->xg / 10 : 0;
            }
        }

        foreach ($keepers as $name => $keeper)
        {
            $known = count($keeper['known']);

            $keepers[$name]['games']     = count($keeper['games']);
            $keepers[$name]['known']     = $known;
            $keepers[$name]['xgFaced']   = $known ? round($keeper['xgFaced'], 1) : null;
            $keepers[$name]['ga']        = $known ? $keeper['ga'] : null;
            $keepers[$name]['savePct']   = $known && $keeper['shots']
                ? round((($keeper['shots'] - $keeper['ga']) / $keeper['shots']) * 100)
                : null;
            $keepers[$name]['prevented'] = $known
                ? round($keeper['xgFaced'] - $keeper['ga'], 1)
                : null;

            // Nothing on the player's row needs the raw shot count once the
            // percentage is worked out.
            unset($keepers[$name]['shots']);
        }

        uasort($keepers, fn ($a, $b) => $b['saves'] <=> $a['saves']);

        return $keepers;
    }

    /**
     * Who kept goal, and when, in each game.
     *
     * `additional` on a start or sub_in holds the position, so 'G' is how a
     * keeper announces themselves.  A sub_out closes their span; a span left
     * open runs to the end of the game.  Games where nobody is marked in goal
     * get no entry at all, which is what marks them as unattributable.
     *
     * @param  \Illuminate\Support\Collection $events
     * @return array
     */
    private function keeperSpans($events)
    {
        $spans = [];

        $onField = [Event::start->value, Event::sub_in->value];

        foreach ($events->groupBy('result_id') as $resultId => $resultEvents)
        {
            // The events come back in insertion order, which is close to but
            // not necessarily the order they happened in.
            $ordered = $resultEvents->sort(function ($a, $b) {
                $cmp = eventTimeToSeconds($a->time) <=> eventTimeToSeconds($b->time);

                return $cmp !== 0 ? $cmp : ($a->id <=> $b->id);
            });

            foreach ($ordered as $event)
            {
                if ($event->against || !$event->player_id)
                {
                    continue;
                }

                if (in_array($event->event_id, $onField) && $event->additional === 'G')
                {
                    $spans[$resultId][] = [
                        'player' => $event->player_name,
                        'start'  => eventTimeToSeconds($event->time),
                        'end'    => null,
                    ];
                }

                if ($event->event_id == Event::sub_out->value && isset($spans[$resultId]))
                {
                    foreach ($spans[$resultId] as $i => $span)
                    {
                        if ($span['end'] === null && $span['player'] === $event->player_name)
                        {
                            $spans[$resultId][$i]['end'] = eventTimeToSeconds($event->time);
                        }
                    }
                }
            }
        }

        return $spans;
    }

    /**
     * The keeper on the field at a given time.
     *
     * @param  array  $spans
     * @param  string $time
     * @return string|null
     */
    private function keeperAt(array $spans, $time)
    {
        $secs = eventTimeToSeconds($time);

        foreach ($spans as $span)
        {
            if ($secs >= $span['start'] && ($span['end'] === null || $secs <= $span['end']))
            {
                return $span['player'];
            }
        }

        return null;
    }
}
