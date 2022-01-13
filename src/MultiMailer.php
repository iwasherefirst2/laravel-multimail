<?php

namespace IWasHereFirst2\LaravelMultiMail;

use \Illuminate\Mail\Mailer;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Traits\Macroable;
use Swift_Mailer;
use Swift_Plugins_AntiFloodPlugin;

class MultiMailer
{
    use Macroable;

    /**
     * Plugins for Swift_Mailer
     * @var array
     */
    protected $plugins;

    /**
     * Mailers
     * @var array
     */
    protected $mailers;

    /**
     * @var FileConfigMailSettings
     */
    private $config;

    /**
     * MultiMailer constructor.
     * @param FileConfigMailSettings $config
     */
    public function __construct(MailSettings $config)
    {
        $this->config = $config;
    }

    /**
     * Create mailer from config/multimail.php
     * If its not a log driver, add AntiFloodPlugin.
     *
     * @param  mixed $key  string or array
     * @param  int timeout
     * @param  int frequency
     * @return \Illuminate\Mail\Mailer
     */
    public function getMailer($key, $timeout = null, $frequency = null, $fromName = null)
    {
        $config = $this->config->initialize($key);

        if (isset($this->mailers[$config->getEmail()])) {
            return $this->mailers[$config->getEmail()];
        }

        $swift_mailer = $this->getSwiftMailer($config);

        if (!$config->isLogDriver() && !empty($timeout) && !empty($frequency)) {
            $this->plugins[] = new Swift_Plugins_AntiFloodPlugin($frequency, $timeout);
        }

        $this->registerPlugins($swift_mailer);

        $view   = app()->get('view');
        $events = app()->get('events');

        if (version_compare(app()->version(), '7.0.0') >= 0) {
            $mailer = new Mailer(config('app.name'), $view, $swift_mailer, $events);
        } else {
            $mailer = new Mailer($view, $swift_mailer, $events);
        }

        $mailer->alwaysFrom($config->getFromEmail(), $fromName ?? $config->getFromName());

        if (!empty($reply_mail = $config->getReplyEmail())) {
            $mailer->alwaysReplyTo($reply_mail, $config->getReplyEmail());
        }

        $this->mailers[$config->getEmail()] = $mailer;

        return $mailer;
    }

    /**
     * Send mail throug mail account form $mailer_name
     * @param  MailableContract $mailable
     * @param  [type]           $mailer_name ]
     * @return [type]                        [description]
     */
    public function sendMail(MailableContract $mailable, $mailer_name, $fromName)
    {
        if (\App::runningUnitTests() && config('multimail.use_default_mail_facade_in_tests')) {
            return \Mail::send($mailable);
        }

        if (empty($mailer_name)) {
            return \Mail::send($mailable);
        }
        $mailer = $this->getMailer($mailer_name, null, null, $fromName);

        $mailable->send($mailer);
    }

    /**
     * [registerPlugin description]
     * @param  [type] $plugin [description]
     * @return [type]         [description]
     */
    public function registerPlugin($plugin)
    {
        $this->plugins[] = $plugin;

        // clear stored mailers, they may need new plugins.
        $this->mailers = [];
    }

    /**
     * [registerPlugin description]
     * @param  [type] $plugin [description]
     * @return [type]         [description]
     */
    public function clearPlugins()
    {
        $this->plugins = [];

        // clear stored mailers, they may need new plugins.
        $this->mailers = [];
    }

    public function getPlugins()
    {
        return $this->plugins;
    }

    public function queueMail(MailableContract $mailable, $mailer_name, $fromName)
    {
        // no mailer given, use default mailer
        if (empty($mailer_name)) {
            return \Mail::queue($mailable);
        }

        Jobs\SendMailJob::dispatch($mailer_name, $mailable, $fromName)->onQueue($mailable->queue ?? null);
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
    public function from(string $mailerKey, $fromName = null)
    {
        return (new PendingMail())->from($mailerKey, $fromName);
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
    protected function getSwiftMailer($config)
    {
        if ($config->isLogDriver()) {
            $transport = TransportManager::createLogDriver();

            return new Swift_Mailer($transport);
        }

        $transport = TransportManager::createSmtpDriver($config);

        return new Swift_Mailer($transport);
    }

    protected function registerPlugins($swift_mailer)
    {
        if (!empty($this->plugins)) {
            foreach ($this->plugins as $plugin) {
                $swift_mailer->registerPlugin($plugin);
            }
        }
    }
}
