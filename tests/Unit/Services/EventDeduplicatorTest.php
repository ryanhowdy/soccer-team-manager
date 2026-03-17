<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\EventDeduplicator;
use App\Models\ResultEvent;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class EventDeduplicatorTest extends TestCase
{
    private EventDeduplicator $deduplicator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deduplicator = new EventDeduplicator;
    }

    /**
     * Build a ResultEvent with the given attributes without triggering
     * any DB connection. We bypass eager loading by unsetting $with,
     * and set created_at as a raw attribute string so the datetime
     * cast is handled by Carbon when accessed.
     */
    private function makeMutableEvent(array $attrs): ResultEvent
    {
        $defaults = [
            'event_id'        => 1,
            'player_id'       => 1,
            'additional'      => null,
            'against'         => 0,
            'created_user_id' => 1,
            'created_at'      => '2026-03-16 10:00:00',
            'time'            => '10:00:00',
            'dedupe_status'   => null,
        ];

        $data = array_merge($defaults, $attrs);

        // If created_at was passed as a Carbon instance, convert to string
        if ($data['created_at'] instanceof Carbon)
        {
            $data['created_at'] = $data['created_at']->format('Y-m-d H:i:s');
        }

        $event = new class extends ResultEvent {
            protected $with = [];
            protected $appends = [];
            public function getDateFormat() { return 'Y-m-d H:i:s'; }
        };

        $event->setRawAttributes([
            'event_id'        => $data['event_id'],
            'player_id'       => $data['player_id'],
            'additional'      => $data['additional'],
            'against'         => $data['against'],
            'created_user_id' => $data['created_user_id'],
            'created_at'      => $data['created_at'],
            'time'            => $data['time'],
            'dedupe_status'   => $data['dedupe_status'],
        ]);

        // Set userRolesManagedPlayers to null so getPriorityScore returns 0
        $event->setRelation('userRolesManagedPlayers', null);

        return $event;
    }

    //
    // No-mark scenarios
    //

    public function test_no_events_returns_empty_collection(): void
    {
        $result = $this->deduplicator->dedupe(new Collection);

        $this->assertCount(0, $result);
    }

    public function test_single_event_is_not_marked(): void
    {
        $event = $this->makeMutableEvent([]);

        $result = $this->deduplicator->dedupe(new Collection([$event]));

        $this->assertCount(1, $result);
        $this->assertNull($result->first()->dedupe_status);
    }

    public function test_same_user_events_are_not_marked(): void
    {
        $e1 = $this->makeMutableEvent(['created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00']);
        $e2 = $this->makeMutableEvent(['created_user_id' => 1, 'created_at' => '2026-03-16 10:00:10']);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $this->assertNull($result[0]->dedupe_status);
        $this->assertNull($result[1]->dedupe_status);
    }

    public function test_events_more_than_30_seconds_apart_are_not_marked(): void
    {
        $e1 = $this->makeMutableEvent(['created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00']);
        $e2 = $this->makeMutableEvent(['created_user_id' => 2, 'created_at' => '2026-03-16 10:00:31']);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $this->assertNull($result[0]->dedupe_status);
        $this->assertNull($result[1]->dedupe_status);
    }

    public function test_different_against_values_are_not_marked(): void
    {
        $e1 = $this->makeMutableEvent(['against' => 0, 'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00']);
        $e2 = $this->makeMutableEvent(['against' => 1, 'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:05']);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $this->assertNull($result[0]->dedupe_status);
        $this->assertNull($result[1]->dedupe_status);
    }

    public function test_unrelated_event_types_are_not_marked(): void
    {
        // goal (1) and corner_kick (12) are not in any similar group
        $e1 = $this->makeMutableEvent(['event_id' => 1,  'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00']);
        $e2 = $this->makeMutableEvent(['event_id' => 12, 'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:05']);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $this->assertNull($result[0]->dedupe_status);
        $this->assertNull($result[1]->dedupe_status);
    }

    //
    // Duplicate scenarios
    //

    public function test_exact_duplicate_is_marked_duplicate(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5, 'additional' => null,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5, 'additional' => null,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $markedCount = $result->whereNotNull('dedupe_status')->count();
        $this->assertEquals(1, $markedCount);

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertEquals('duplicate', $marked->dedupe_status);
    }

    public function test_duplicate_with_same_additional_is_marked_duplicate(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5, 'additional' => '8',
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5, 'additional' => '8',
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:15',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertEquals('duplicate', $marked->dedupe_status);
    }

    //
    // Wrong scenarios
    //

    public function test_same_event_type_different_player_is_marked_wrong(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 7,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertNotNull($marked);
        $this->assertEquals('wrong', $marked->dedupe_status);
    }

    public function test_same_event_type_different_additional_is_marked_wrong(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5, 'additional' => '8',
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5, 'additional' => '9',
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertEquals('wrong', $marked->dedupe_status);
    }

    public function test_goal_vs_penalty_goal_is_marked_wrong(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 19, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertEquals('wrong', $marked->dedupe_status);
    }

    public function test_goal_vs_free_kick_goal_is_marked_wrong(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 22, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertEquals('wrong', $marked->dedupe_status);
    }

    public function test_shot_on_vs_shot_off_is_marked_wrong(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 6, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 7, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertEquals('wrong', $marked->dedupe_status);
    }

    public function test_penalty_on_vs_penalty_off_is_marked_wrong(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 20, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 21, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertEquals('wrong', $marked->dedupe_status);
    }

    public function test_free_kick_on_vs_free_kick_off_is_marked_wrong(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 23, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 24, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertEquals('wrong', $marked->dedupe_status);
    }

    public function test_shot_on_target_vs_penalty_on_target_is_marked_wrong(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 6, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 20, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $marked = $result->firstWhere('dedupe_status', '!=', null);
        $this->assertEquals('wrong', $marked->dedupe_status);
    }

    //
    // Priority scenarios
    //

    public function test_newer_event_is_marked_when_same_priority(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        // e1 is older, so e2 (newer) should be marked
        $this->assertNull($result[0]->dedupe_status);
        $this->assertEquals('duplicate', $result[1]->dedupe_status);
    }

    //
    // Collection integrity
    //

    public function test_all_events_remain_in_collection(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:10',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $this->assertCount(2, $result);
    }

    public function test_events_at_exactly_30_seconds_are_still_compared(): void
    {
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:30',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $markedCount = $result->whereNotNull('dedupe_status')->count();
        $this->assertEquals(1, $markedCount);
    }

    //
    // Multiple events
    //

    public function test_three_events_only_marks_one_per_pair(): void
    {
        // Two users enter the same goal, third user enters a corner kick
        $e1 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 1, 'player_id' => 5,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:05',
        ]);
        $e3 = $this->makeMutableEvent([
            'event_id' => 12, 'player_id' => 3,
            'created_user_id' => 3, 'created_at' => '2026-03-16 10:00:08',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2, $e3]));

        $this->assertCount(3, $result);
        $markedCount = $result->whereNotNull('dedupe_status')->count();
        $this->assertEquals(1, $markedCount);
        $this->assertNull($result[2]->dedupe_status);
    }

    public function test_against_events_are_deduped_separately(): void
    {
        // Same event for "us" and "them" should NOT be compared
        $e1 = $this->makeMutableEvent([
            'event_id' => 12, 'player_id' => 5, 'against' => 0,
            'created_user_id' => 1, 'created_at' => '2026-03-16 10:00:00',
        ]);
        $e2 = $this->makeMutableEvent([
            'event_id' => 12, 'player_id' => 5, 'against' => 1,
            'created_user_id' => 2, 'created_at' => '2026-03-16 10:00:05',
        ]);

        $result = $this->deduplicator->dedupe(new Collection([$e1, $e2]));

        $this->assertNull($result[0]->dedupe_status);
        $this->assertNull($result[1]->dedupe_status);
    }
}
