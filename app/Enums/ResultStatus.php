<?php

namespace App\Enums;

enum ResultStatus: string
{
    case Scheduled            = 'S';
    case Cancelled            = 'C';
    case Done                 = 'D';
}
