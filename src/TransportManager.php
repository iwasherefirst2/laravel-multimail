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

        return self::configureSmtpDriver($transport, $provider);
    }

    /**
     * Configure the additional SMTP driver options.
     *
     * @param  \Swift_SmtpTransport  $transport
     * @param  array  $config
     * @return \Swift_SmtpTransport
     */
    protected static function configureSmtpDriver($transport, $config)
    {
        if (isset($config['stream'])) {
            $transport->setStreamOptions($config['stream']);
        }

        if (isset($config['source_ip'])) {
            $transport->setSourceIp($config['source_ip']);
        }

        if (isset($config['local_domain'])) {
            $transport->setLocalDomain($config['local_domain']);
        }

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
