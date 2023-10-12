<?php

namespace IWasHereFirst2\LaravelMultiMail;

use Illuminate\Mail\Transport\LogTransport;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class TransportManager
{
    /**
     * Create SMTP Transport.
     *
     * @param  array
     * @return Dsn
     */
    public static function createSmtpDriver($config)
    {
        $provider = $config->getProvider();
        $setting  = $config->getSetting();

        $transport_factory = new EsmtpTransportFactory;
        $transport = $transport_factory->create(new Dsn(
            'smtp',
            $provider['host'],
            $setting['username'],
            $setting['pass'],
            $provider['port']
        ));

        return self::configureSmtpDriver($transport, $provider);
    }

    /**
     * Configure the additional SMTP driver options.
     *
     * @param  EsmtpTransportFactory  $transport
     * @param  array  $config
     * @return EsmtpTransportFactory
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
