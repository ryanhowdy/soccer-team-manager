<?php

namespace App\Enums;

/**
 * The seasons a year is split into.
 *
 * A closed set: this was a free-text varchar, which let casing and spelling
 * drift into a column the grade calculation depends on reading exactly.
 */
enum SeasonName: string
{
    case Fall   = 'Fall';
    case Spring = 'Spring';

    /**
     * Values for a form <select>
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
