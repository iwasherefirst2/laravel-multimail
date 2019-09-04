<?php

namespace IWasHereFirst2\LaravelMultiMail\Facades;

use Illuminate\Support\Facades\Facade;

class MultiMail extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'iwasherefirst2-laravelmultimail';
  }
}
