<?php

use App\Models\ResultEvent;
use App\Enums\Event;
use App\Enums\ResultStatus;

/*
 * Helpers
 *
 * Some usefule global helper/utility functions
 */

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
