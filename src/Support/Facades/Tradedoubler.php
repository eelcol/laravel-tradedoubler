<?php

namespace Eelcol\LaravelTradedoubler\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Tradedoubler extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tradedoubler';
    }
}