<?php

namespace App\Services;

use App\Models\ResultEvent;
use Illuminate\Database\Eloquent\Collection;

class EventDeduplicator
{
    /**
     * Similar event groups where different event types represent
     * variations of the same real-world action.
     */
    private const SIMILAR_EVENT_GROUPS = [
        [1, 19, 22],  // goal, penalty_goal, free_kick_goal
        [6, 20, 23],  // shot_on_target, penalty_on_target, free_kick_on_target
        [7, 21, 24],  // shot_off_target, penalty_off_target, free_kick_off_target
        [6, 7],       // shot_on_target, shot_off_target
        [20, 21],     // penalty_on_target, penalty_off_target
        [23, 24],     // free_kick_on_target, free_kick_off_target
    ];

    /**
     * dedupe
     *
     * Takes a collection of result events, sorts by time ascending,
     * and marks duplicate or wrong events. Two events from different users
     * created within 30 seconds are compared:
     *
     * - "duplicate": same event_id, player_id, additional, and against value
     * - "wrong": same against value and either:
     *     - same event_id but different player_id or additional
     *     - similar event_ids (e.g. goal vs penalty_goal)
     *
     * The lower-priority event gets its dedupe_status set.
     * Events are NOT removed from the collection.
     *
     * @param Collection $events
     * @return Collection
     */
    public function dedupe(Collection $events): Collection
    {
        $events = $events->sortBy(function ($e) {
            return eventTimeToSeconds($e->time);
        });

        $processed = [];

        foreach ($events as $eventKey => $e)
        {
            foreach ($processed as $otherKey => $otherEvent)
            {
                // must be different users
                if ($e->created_user_id == $otherEvent->created_user_id)
                {
                    continue;
                }

                // must be within 30 seconds
                if (abs($e->created_at->diffInSeconds($otherEvent->created_at)) > 30)
                {
                    continue;
                }

                // must be same team (against value)
                if ($e->against != $otherEvent->against)
                {
                    continue;
                }

                // skip if the other event is already marked
                if ($otherEvent->dedupe_status !== null)
                {
                    continue;
                }

                $status = $this->compareEvents($e, $otherEvent);

                if ($status !== null)
                {
                    $keyToMark = $this->getLowestPriorityKey($eventKey, $e, $otherKey, $otherEvent);

                    $events[$keyToMark]->dedupe_status = $status;
                    break;
                }
            }

            $processed[$eventKey] = $e;
        }

        return $events;
    }

    /**
     * compareEvents
     *
     * Determine the relationship between two events that are already
     * known to be from different users, within 30s, and same team.
     *
     * @param ResultEvent $a
     * @param ResultEvent $b
     * @return string|null  'duplicate', 'wrong', or null
     */
    private function compareEvents(ResultEvent $a, ResultEvent $b): ?string
    {
        if ($a->event_id == $b->event_id)
        {
            if ($a->player_id == $b->player_id && $a->additional == $b->additional)
            {
                return 'duplicate';
            }

            return 'wrong';
        }

        if ($this->areEventsSimilar($a->event_id, $b->event_id))
        {
            return 'wrong';
        }

        return null;
    }

    /**
     * areEventsSimilar
     *
     * Returns true if two event_id values belong to the same similar group.
     *
     * @param int $eventId1
     * @param int $eventId2
     * @return bool
     */
    private function areEventsSimilar(int $eventId1, int $eventId2): bool
    {
        foreach (self::SIMILAR_EVENT_GROUPS as $group)
        {
            if (in_array($eventId1, $group) && in_array($eventId2, $group))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * getLowestPriorityKey
     *
     * Determines which of two events should be marked.
     * Priority (highest to lowest):
     *   1. Created user has a managed player and the event is for that player
     *   2. Created user has the admin role
     *   3. Created user has the manager role
     *   4. The event that was created oldest (earliest created_at)
     *
     * @param int $key1
     * @param ResultEvent $event1
     * @param int $key2
     * @param ResultEvent $event2
     * @return int
     */
    private function getLowestPriorityKey(int $key1, ResultEvent $event1, int $key2, ResultEvent $event2): int
    {
        $score1 = $this->getPriorityScore($event1);
        $score2 = $this->getPriorityScore($event2);

        if ($score1 !== $score2)
        {
            return $score1 < $score2 ? $key1 : $key2;
        }

        // same priority - keep the oldest, mark the newer one
        return $event1->created_at->lte($event2->created_at) ? $key2 : $key1;
    }

    /**
     * getPriorityScore
     *
     * Returns a numeric priority score for a result event.
     *   3 = created user has managed player matching the event player
     *   2 = created user has admin role
     *   1 = created user has manager role
     *   0 = default
     *
     * @param ResultEvent $event
     * @return int
     */
    private function getPriorityScore(ResultEvent $event): int
    {
        $user = $event->userRolesManagedPlayers;

        if ($user && $user->managedPlayers->count() && $event->player_id)
        {
            foreach ($user->managedPlayers as $p)
            {
                if ($p->player_id == $event->player_id)
                {
                    return 3;
                }
            }
        }

        if ($user && $user->hasRole('admin'))
        {
            return 2;
        }

        if ($user && $user->hasRole('manager'))
        {
            return 1;
        }

        return 0;
    }
}
