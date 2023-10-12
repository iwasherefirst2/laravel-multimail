<?php

namespace IWasHereFirst2\LaravelMultiMail\Exceptions;

class InvalidConfigKeyException extends \Exception
{
    public function __construct($message = null)
    {
        $this->message =  $message ?? "Mailer name has to be provided in array as column 'email'";
        parent::__construct();
    }
}
