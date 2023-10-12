<?php

namespace IWasHereFirst2\LaravelMultiMail;

use Illuminate\Support\Arr;
use Illuminate\Support\ConfigurationUrlParser;

// Methods are stolen from
// Illuminate\Mail\MailManager
class DefaultLaravelMailDriver
{
    public function getDefaultDriver()
    {
        // Here we will check if the "driver" key exists and if it does we will use
        // that as the default driver in order to provide support for old styles
        // of the Laravel mail configuration file for backwards compatibility.
        return config('mail.driver') ??
            config('mail.default');
    }

    public function getDefaultLaravelConfig()
    {
        $driver = $this->getDefaultDriver();
        return $this->getDefaultLaravelConfig($driver);
    }

    public function getLaravelConfig(string $name): array|null
    {
        // Here we will check if the "driver" key exists and if it does we will use
        // the entire mail configuration file as the "driver" config in order to
        // provide "BC" for any Laravel <= 6.x style mail configuration files.
        $config = config('mail.driver')
            ? config('mail')
            : config("mail.mailers.{$name}");

        if (isset($config['url'])) {
            $config = array_merge($config, (new ConfigurationUrlParser)->parseConfiguration($config));

            $config['transport'] = Arr::pull($config, 'driver');
        }

        return $config;
    }
}
