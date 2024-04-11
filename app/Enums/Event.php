<?php

namespace App\Enums;

enum Event: int 
{
    case goal                 = 1;
    case start                = 2;
    case goal_against         = 3;
    case shot_on_target       = 4;
    case shot_off_target      = 5;
    case tackle_won           = 6;
    case tackle_lost          = 7;
    case save                 = 8;
    case shot_against         = 9;
    case corner_kick          = 10;
    case corner_kick_against  = 11;
    case offsides             = 12;
    case foul                 = 13;
    case fouled               = 14;
    case yellow_card          = 15;
    case red_card             = 16;
    case penalty_goal         = 17;
    case penalty_on_target    = 18;
    case penalty_off_target   = 19;
    case free_kick_goal       = 20;
    case free_kick_on_target  = 21;
    case free_kick_off_target = 22;
}
