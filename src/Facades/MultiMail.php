<?php

namespace IWasHereFirst2\LaravelMultiMail\Facades;

use Illuminate\Support\Facades\Facade;
use IWasHereFirst2\LaravelMultiMail\MultiMailer;

class MultiMail extends Facade
{
    /**
    *
    * @return string
    */
    protected static function getFacadeAccessor()
    {
        return MultiMailer::class;
    }
}
