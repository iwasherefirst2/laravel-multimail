<?php

namespace IWasHereFirst2\LaravelMultiMail;

use Illuminate\Mail\Mailer;
use IWasHereFirst2\LaravelMultiMail\Exceptions\InvalidConfigKeyException;

class MailManager extends \Illuminate\Mail\MailManager
{
    protected $mailers = [];

    public function getMailer(MailSettings $settings)
    {
        if (isset($this->mailers[$settings->getEmail()])) {
            return $this->mailers[$settings->getEmail()];
        }

        return $this->mailers[$settings->getEmail()] = $this->resolveMulti($settings);
    }

    private function resolveMulti(MailSettings $settings)
    {
        $config = $settings->getDriver();

        if (is_null($config)) {
            throw new InvalidConfigKeyException("Mailer for [{$settings->getEmail()}] is not defined.");
        }

        // Once we have created the mailer instance we will set a container instance
        // on the mailer. This allows us to resolve mailer classes via containers
        // for maximum testability on said classes instead of passing Closures.
        $mailer = new Mailer(
            $settings->getDriverName(),
            $this->app['view'],
            $this->createSymfonyTransport($config),
            $this->app['events']
        );

        if ($this->app->bound('queue')) {
            $mailer->setQueue($this->app['queue']);
        }

        // Next we will set all of the global addresses on this mailer, which allows
        // for easy unification of all "from" addresses as well as easy debugging
        // of sent messages since these will be sent to a single email address.
        foreach (['reply_to', 'to', 'return_path'] as $type) {
            $this->setGlobalAddress($mailer, $config, $type);
        }

        $mailer->alwaysFrom($settings->getEmail(), $settings->getFromName());

        if ($settings->getReplyTo() != null) {
            $mailer->alwaysReplyTo($settings->getReplyTo());
        }

        if ($settings->getReturnPath() != null) {
            $mailer->alwaysReturnPath($settings->getReturnPath());
        }

        return $mailer;
    }
}
