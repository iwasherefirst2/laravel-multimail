<?php

namespace IWasHereFirst2\LaravelMultiMail\Exceptions;

class EmailNotInConfigException extends \Exception
{
    public function __construct($mail)
    {
        $this->message = 'Email ' . $mail . ' not found in config/multimail.php!';
        parent::__construct();
    }
}
