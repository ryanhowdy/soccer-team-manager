<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ResultEvent;
use App\Enums\Event;

class FixAgainstEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-against-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will fix old against event data (v1.0.1 or earlier)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating goals...');

        // Change goal_against events to goal events with an against flag
        ResultEvent::where('event_id', Event::goal_against->value)
            ->update([
                'event_id'  => Event::goal->value,
                'player_id' => null,
                'against'   => 1,
            ]);

        $this->info('Updating shots...');

        // Change shot_against events to shot events with an against flag
        ResultEvent::where('event_id', Event::shot_against->value)
            ->update([
                'event_id'  => Event::shot_off_target->value,
                'player_id' => null,
                'against'   => 1,
            ]);

        $this->info('Updating corner kicks...');

        // Change corner_kick_against events to corner_kick events with an against flag
        ResultEvent::where('event_id', Event::corner_kick_against->value)
            ->update([
                'event_id'  => Event::corner_kick->value,
                'player_id' => null,
                'against'   => 1,
            ]);
    }
}
