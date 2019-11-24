<?php

namespace IWasHereFirst2\LaravelMultiMail;

use \Illuminate\Mail\Mailer;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Mail\Mailable;
use Swift_Mailer;
use Swift_Plugins_AntiFloodPlugin;

class MultiMailer
{
    /**
     * Plugins for Swift_Mailer
     * @var array
     */
    protected static $plugins;

    /**
     * Create mailer from config/multimail.php
     * If its not a log driver, add AntiFloodPlugin.
     *
     * @param  mixed $key  string or array
     * @param  int timeout
     * @param  int frequency
     * @return \Illuminate\Mail\Mailer
     */
    public static function getMailer($key, $timeout = null, $frequency = null)
    {
        $config = new Config($key);

        $swift_mailer = static::getSwiftMailer($config);

        if (!$config->isLogDriver() && !empty($timeout) && !empty($frequency)) {
            static::$plugins[] = new Swift_Plugins_AntiFloodPlugin($frequency, $timeout);
        }

        static::registerPlugins($swift_mailer);

        $view   = app()->get('view');
        $events = app()->get('events');
        $mailer = new Mailer($view, $swift_mailer, $events);

        $mailer->alwaysFrom($config->getFromEmail(), $config->getFromName());

        if (!empty($reply_mail = $config->getReplyEmail())) {
            $mailer->alwaysReplyTo($reply_mail, $config->getReplyEmail());
        }

        return $mailer;
    }

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

    /**
     * [registerPlugin description]
     * @param  [type] $plugin [description]
     * @return [type]         [description]
     */
    public static function registerPlugin($plugin)
    {
        static::$plugins[] = $plugin;
    }

    /**
     * [registerPlugin description]
     * @param  [type] $plugin [description]
     * @return [type]         [description]
     */
    public static function clearPlugins()
    {
        static::$plugins = [];
    }

    public static function getPlugins()
    {
        return static::$plugins;
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
     * Create SwiftMailer with timeout/frequency. Timeout/frequency is ignored
     * when Log Driver is used.
     *
     * @param  array
     * @return Swift_Mailer
     */
    protected static function getSwiftMailer($config)
    {
        if ($config->isLogDriver()) {
            $transport = TransportManager::createLogDriver();

            return new Swift_Mailer($transport);
        }

        $transport = TransportManager::createSmtpDriver($config);

        return new Swift_Mailer($transport);
    }

    protected static function registerPlugins($swift_mailer)
    {
        if (!empty(static::$plugins)) {
            foreach (static::$plugins as $plugin) {
                $swift_mailer->registerPlugin($plugin);
            }
        }
    }
}
