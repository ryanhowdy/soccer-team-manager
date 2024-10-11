<?php

namespace App\Chart;

use Illuminate\Support\Facades\Facade;

class ChartFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chart';
    }
}
