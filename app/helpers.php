<?php

use App\Models\ResultEvent;
use App\Enums\Event;

/*
 * Helpers
 *
 * Some usefule global helper/utility functions
 */

if (!function_exists('getChartDataFromResults'))
{
    /*
     * getChartDataFromResults
     *
     * Given a collection of results, will calculate the following stats.
     * - win/draw/loss
     * - goals by player
     * - assists by player
     * - goals per game
     * - goals allowed per game
     *
     * @param Collection $results
     * @return array
     */
    function getChartDataFromResults(Illuminate\Database\Eloquent\Collection $results, $teamId)
    {
        // Figure out the chart data based on the results
        $chartData = [
            'wdl'     => ['w' => 0, 'd' => 0, 'l' => 0],
            'gpg'     => ['goals' => 0, 'games' => 0, 'gpg' => 0],
            'gapg'    => ['allowed' => 0, 'games' => 0, 'gapg' => 0],
            'goals'   => ['players' => [], 'labels' => '', 'data' => ''],
            'assists' => ['players' => [], 'labels' => '', 'data' => ''],
        ];

        $resultIds = [];

        foreach ($results as $result)
        {
            $resultIds[$result->id] = $result->id;

            $goodGuys = $result->home_team_id == $teamId ? 'home' : 'away';
            $badGuys  = $goodGuys === 'home'                              ? 'away' : 'home';

            // win/draw/loss
            if ($result->{$goodGuys . '_team_score'} > $result->{$badGuys . '_team_score'})
            {
                $chartData['wdl']['w']++;
            }
            else if ($result->{$goodGuys . '_team_score'} < $result->{$badGuys . '_team_score'})
            {
                $chartData['wdl']['l']++;
            }
            else
            {
                $chartData['wdl']['d']++;
            }

            // goal per game
            $chartData['gpg']['goals'] += $result->{$goodGuys . '_team_score'};
            $chartData['gpg']['games']++;

            // goals allowed per game
            $chartData['gapg']['allowed'] += $result->{$badGuys . '_team_score'};
            $chartData['gapg']['games']++;
        }

        if ($chartData['gpg']['games'])
        {
            $chartData['gpg']['gpg']   = round($chartData['gpg']['goals'] / $chartData['gpg']['games'], 2);
            $chartData['gapg']['gapg'] = round($chartData['gapg']['allowed'] / $chartData['gapg']['games'], 2);
        }

        // Get player goals/assists
        $events = [];
        $events = ResultEvent::whereIn('result_id', $resultIds)
            ->where('event_id', Event::goal->value)
            ->get();

        foreach ($events as $event)
        {
            // goals
            if (!isset($chartData['goals']['players'][$event->player_name]))
            {
                $chartData['goals']['players'][$event->player_name] = 0;
            }

            $chartData['goals']['players'][$event->player_name]++;

            // assists
            if (!empty($event->additional))
            {
                if (!isset($chartData['assists']['players'][$event->player_name]))
                {
                    $chartData['assists']['players'][$event->player_name] = 0;
                }

                $chartData['assists']['players'][$event->player_name]++;
            }

        }

        // Sort goals and assits descending, then by player name alphabetical
        array_multisort(array_values($chartData['goals']['players']), SORT_DESC, array_keys($chartData['goals']['players']), SORT_ASC, $chartData['goals']['players']);
        array_multisort(array_values($chartData['assists']['players']), SORT_DESC, array_keys($chartData['assists']['players']), SORT_ASC, $chartData['assists']['players']);

        $chartData['goals']['labels'] = "'" . implode("','", array_keys($chartData['goals']['players'])) . "'";
        $chartData['goals']['data']   = implode(',', array_values($chartData['goals']['players']));
        $chartData['assists']['labels'] = "'" . implode("','", array_keys($chartData['assists']['players'])) . "'";
        $chartData['assists']['data']   = implode(',', array_values($chartData['assists']['players']));

        return $chartData;
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
     * Will display seconds in whole minutes, rounding up.
     * So 15 mins and 1 second will be displayed as 16 mins.
     *
     * @param int $seconds
     * @return int
     */
    function secondsToMinutes($time)
    {
        return ceil($time / 60);
    }
}
