<?php

namespace IWasHereFirst2\LaravelMultiMail;

use \Illuminate\Mail\Mailer;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Transport\LogTransport;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_SmtpTransport;

class MultiMailer
{
    protected $mailer;

    protected $locale;

    /**
     * Send mail throug mail account form $mailer_name
     * @param  MailableContract $mailable
     * @param  [type]           $mailer_name ]
     * @return [type]                        [description]
     */
    public static function sendMail(MailableContract $mailable, $mailer_name)
    {
        // no mailer given, use default mailer
        if (empty($mailer_name)) {
            return \Mail::send($mailable);
        }

        $mailer = static::getMailer($mailer_name);
        $mailable->send($mailer);
    }

    public static function queueMail(MailableContract $mailable, $mailer_name)
    {
        // no mailer given, use default mailer
        if (empty($mailer_name)) {
            return \Mail::queue($mailable);
        }
        Jobs\SendMailJob::dispatch($mailer_name, $mailable);
    }

    /**
     * Create mailer from config/multimail.php
     * @param  mixed $name  string or array
     * @param  int timeout
     * @param  int frequency
     * @return \Illuminate\Mail\Mailer
     */
    public static function getMailer($key, $timeout = null, $frequency = null)
    {
        if (is_array($key)) {
            $from_name = $key['name'] ?? null;

            if (empty($key['email'])) {
                throw new \Exception("Mailer name has to be provided in array as column 'email' ", 1);
            }
            $email = $key['email'];
        } else {
            $email = $key;
        }

        $config   = config('multimail.emails')[$email];

        if (empty($email) || empty($config) || empty($config['pass']) || empty($config['username'])) {
            $config = config('multimail.emails.default');

            $provider = static::getProvider($config['provider'] ?? null);

            if ($provider['driver'] != 'log' && (empty($config) || empty($config['pass']) || empty($config['username']))) {
                // No need for pass/username when using log-driver
                throw new \Exception('Configuration for email: ' . $email . ' is missing in config/multimail.php and no default is specified.', 1);
            }
        }

        $swift_mailer = static::getSwiftMailer($config, $timeout = null, $frequency = null);

        $view = app()->get('view');
        $events = app()->get('events');
        $mailer = new Mailer($view, $swift_mailer, $events);

        if (empty($from_name) && !empty($config['from_name'])) {
            $from_name = $config['from_name'];
        }

        $mailer->alwaysFrom($config['from_mail'] ?? $email, $from_name ?? null);

        if (!empty($config['reply_to_mail'])) {
            $mailer->alwaysReplyTo($config['reply_to_mail'], $config['reply_to_name'] ?? null);
        }

        return $mailer;
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \IWasHereFirst2\MultiMail\PendingMail
     */
    public function to($users)
    {
        return (new PendingMail())->to($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \IWasHereFirst2\MultiMail\PendingMail
     */
    public function from($name)
    {
        return (new PendingMail())->from($name);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \IWasHereFirst2\MultiMail\PendingMail
     */
    public function cc($users)
    {
        return (new PendingMail())->cc($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  mixed  $users
     * @return \IWasHereFirst2\MultiMail\PendingMail
     */
    public function bcc($users)
    {
        return (new PendingMail())->bcc($users);
    }

    /**
     * Begin the process of mailing a mailable class instance.
     *
     * @param  string locale 2 char
     * @return \IWasHereFirst2\MultiMail\PendingMail
     */
    public function locale($locale)
    {
        return (new PendingMail())->locale($locale);
    }

    /**
     * Send mail or queue if implements ShouldQueue
     *
     * @param  Mailable
     * @return \IWasHereFirst2\MultiMail\MultiMailer
     */
    public function send(Mailable $mailable)
    {
        return (new PendingMail())->send($mailable);
    }

    /**
     * Queue mail
     *
     * @param  Mailable
     * @return \IWasHereFirst2\MultiMail\MultiMailer
     */
    public function queue(Mailable $mailable)
    {
        return (new PendingMail())->queue($mailable);
    }

    /**
     * Get SMTP Transport
     * @param  array
     * @return Swift_SmtpTransport
     */
    protected static function getSMTPTransport($config)
    {
        $provider = static::getProvider($config['provider']);

        $transport = new Swift_SmtpTransport($provider['host'], $provider['port'], $provider['encryption']);
        $transport->setUsername($config['username']);
        $transport->setPassword($config['pass']);

        return $transport;
    }

    /**
     * Get LOG Transport
     * @return LogTransport
     */
    protected static function getLogTransport()
    {
        return new LogTransport(app()->make(LoggerInterface::class));
    }

    /**
     * Create SwiftMailer with timeout/frequency. Timeout/frequency is ignored
     * when Log Driver is used.
     *
     * @param  array
     * @return Swift_Mailer
     */
    protected static function getSwiftMailer($config, $timeout = null, $frequency = null)
    {
        $provider = static::getProvider($config['provider'] ?? null);

        if (isset($provider['driver']) && $provider['driver'] == 'log') {
            $transport = static::getLogTransport();

            return new Swift_Mailer($transport);
        }

        $transport = static::getSMTPTransport($config);

        $swift_mailer = new Swift_Mailer($transport);

        if (!empty($frequency) && !empty($timeout)) {
            $swift_mailer->registerPlugin(new \Swift_Plugins_AntiFloodPlugin($frequency, $timeout));
        }

        return $swift_mailer;
    }

    /**
     * Get array of provdier (Host/Port/Encyption/Driver).
     * If no provider specified, use default.
     * @param  string provider
     * @return array
     */
    protected static function getProvider($provider = null)
    {
        if (!empty($provider)) {
            $provider = config('multimail.provider.' . $provider);

            if (!empty($provider)) {
                return $provider;
            }
        }

        return config('multimail.provider.default');
    }
}
