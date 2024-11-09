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

    public static function getGoalValues()
    {
        return [
            Event::goal->value, 
            Event::penalty_goal->value, 
            Event::free_kick_goal->value,
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
