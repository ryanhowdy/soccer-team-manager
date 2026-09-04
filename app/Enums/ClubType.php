<?php

namespace App\Enums;

/**
 * What kind of organization a club record represents.
 *
 * A high school is an organization type rather than a team type: every team
 * under a school is a school team, so this lives on clubs and the teams inherit
 * it.
 */
enum ClubType: string
{
    case Club   = 'club';
    case School = 'school';

    /**
     * Label for a form <select> / display
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this)
        {
            self::Club   => 'Club',
            self::School => 'High School',
        };
    }
}
