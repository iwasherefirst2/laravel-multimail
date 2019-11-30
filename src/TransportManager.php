<?php

namespace IWasHereFirst2\LaravelMultiMail;

use Illuminate\Mail\Transport\LogTransport;
use Psr\Log\LoggerInterface;
use Swift_SmtpTransport;

class TransportManager
{
    /**
     * Create SMTP Transport.
     *
     * @param  array
     * @return Swift_SmtpTransport
     */
    public static function createSmtpDriver($config)
    {
        $provider = $config->getProvider();
        $setting  = $config->getSetting();

        $transport = new Swift_SmtpTransport($provider['host'], $provider['port'], $provider['encryption']);
        $transport->setUsername($setting['username'] ?? $config->getEmail());
        $transport->setPassword($setting['pass']);

        return $transport;
    }

    /**
     * Create LOG Transport.
     *
     * @return LogTransport
     */
    public static function createLogDriver()
    {
        return new LogTransport(app()->make(LoggerInterface::class));
    }
}
