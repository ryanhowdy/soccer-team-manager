<?php

namespace App\Enums;

enum Event: int 
{
    case goal                 = 1;
    case start                = 2;
    case sub_in               = 3;
    case sub_out              = 4;
    case shot_on_target       = 6;
    case shot_off_target      = 7;
    case tackle_won           = 8;
    case tackle_lost          = 9;
    case save                 = 10;
    case corner_kick          = 12;
    case offsides             = 14;
    case foul                 = 15;
    case fouled               = 16;
    case yellow_card          = 17;
    case red_card             = 18;
    case penalty_goal         = 19;
    case penalty_on_target    = 20;
    case penalty_off_target   = 21;
    case free_kick_goal       = 22;
    case free_kick_on_target  = 23;
    case free_kick_off_target = 24;
    case halftime             = 25;
    case fulltime             = 26;
    case gain_possession      = 27;
    case lose_possession      = 28;

    public static function getGoalValues()
    {
        return [
            Event::goal->value, 
            Event::penalty_goal->value, 
            Event::free_kick_goal->value,
        ];
    }

    /**
     * The events an assist can be credited on - the 'Ast' stat.
     *
     * A goal, and a free kick goal where someone else supplied the ball (a
     * cross headed in - see the note on `additional` below).  A penalty is
     * never assisted, so penalty_goal is not here.
     */
    public static function getAssistValues()
    {
        return [
            Event::goal->value,
            Event::free_kick_goal->value,
        ];
    }

    /**
     * The events a chance-creating pass can be credited on - the 'Cha' stat.
     *
     * Every goal and shot except the penalty variants, because nobody supplies
     * a penalty.  A corner is neither a goal nor a shot, so it is not here.
     * A pass to a goal counts here as well as being an assist, so chance
     * creation is the full picture of what a player set up.
     *
     * A NOTE ON SET PIECES.  `additional` means "who supplied the ball", and
     * that is what separates a direct free kick from an indirect one.  A direct
     * strike has no `additional` at all - nobody supplied it - so it can never
     * credit anyone here.  An indirect one carries the taker, the cross that
     * got headed in, which is chance creation by any measure.  Listing the
     * free kick events here therefore only ever credits the indirect ones.
     */
    public static function getChanceValues()
    {
        return [
            Event::goal->value,
            Event::shot_on_target->value,
            Event::shot_off_target->value,
            Event::free_kick_goal->value,
            Event::free_kick_on_target->value,
            Event::free_kick_off_target->value,
        ];
    }

    public static function getShotValues()
    {
        return [
            Event::shot_on_target->value,
            Event::penalty_on_target->value,
            Event::free_kick_on_target->value,
            Event::shot_off_target->value,
            Event::penalty_off_target->value,
            Event::free_kick_off_target->value,
        ];
    }

    public static function getShotOnTargetValues()
    {
        return [
            Event::shot_on_target->value,
            Event::penalty_on_target->value,
            Event::free_kick_on_target->value,
        ];
    }

    public static function getShotOffTargetValues()
    {
        return [
            Event::shot_off_target->value,
            Event::penalty_off_target->value,
            Event::free_kick_off_target->value,
        ];
    }

    public static function getFreeKickValues()
    {
        return [
            Event::free_kick_goal->value,
            Event::free_kick_on_target->value,
            Event::free_kick_off_target->value,
        ];
    }

    public static function getPenaltyValues()
    {
        return [
            Event::penalty_goal->value,
            Event::penalty_on_target->value,
            Event::penalty_off_target->value,
        ];
    }
}
