<?php

namespace App\Enums;

enum CompetitionStatus: string
{
    case Active               = 'A';
    case Cancelled            = 'C';
    case Done                 = 'D';
    //case Rescheduled          = 'R';
}
