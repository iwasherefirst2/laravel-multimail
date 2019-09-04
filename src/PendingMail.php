<?php

namespace IWasHereFirst2\LaravelMultiMail;

use Illuminate\Contracts\Queue\ShouldQueue;
use IWasHereFirst2\LaravelMultiMail\MultiMailer;
use Illuminate\Mail\Mailable;

class PendingMail
{
    /**
     * The string of the mailer the user wishes to send from.
     *
     * @var string
     */
    protected $fromMailer;

    /**
     * The locale of the message.
     *
     * @var array
     */
    protected $locale;

    /**
     * The "to" recipients of the message.
     *
     * @var array
     */
    protected $to = [];

    /**
     * The "cc" recipients of the message.
     *
     * @var array
     */
    protected $cc = [];

    /**
     * The "bcc" recipients of the message.
     *
     * @var array
     */
    protected $bcc = [];


    /**
     * Set the locale of the message.
     *
     * @param  string  $locale
     * @return $this
     */
    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set the recipients of the message.
     *
     * @param  mixed  $users
     * @return $this
     */
    public function to($users)
    {
        $this->to = $users;

        return $this;
    }

    /**
     * Determine from mailer.
     *
     * @param  string  mailer name
     * @return $this
     */
    public function from($mailer)
    {
        $this->fromMailer = $mailer;

        return $this;
    }

    /**
     * Set the addtional recipients of the message.
     *
     * @param  mixed  $users
     * @return $this
     */
    public function cc($users)
    {
        $this->cc = $users;

        return $this;
    }

    /**
     * Set the hidden recipients of the message.
     *
     * @param  mixed  $users
     * @return $this
     */
    public function bcc($users)
    {
        $this->bcc = $users;

        return $this;
    }

    /**
     * Send a new mailable message instance.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function send(Mailable $mailable)
    {
        if ($mailable instanceof ShouldQueue) {
            return $this->queue($mailable);
        }

        return MultiMailer::send($this->fill($mailable), $this->fromMailer);
    }

    /**
     * Send a mailable message immediately.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function sendNow(Mailable $mailable)
    {
        return MultiMailer::send($this->fill($mailable), $this->fromMailer);
    }

    /**
     * Push the given mailable onto the queue.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function queue(Mailable $mailable)
    {
        return MultiMailer::queue($this->fill($mailable), $this->fromMailer);
    }

    /**
     * Send a new mailable message instance.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function sendWithTranslatedConstructor($classname, $parameter)
    {
        return $this->send($this->createMailable($classname, $parameter));
    }

    /**
     * Push the given mailable onto the queue.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function queueWithTranslatedConstructor(Mailable $mailable)
    {
      return $this->queue($this->createMailable($classname, $parameter));
    }

    /**
     * Create mailable by classname and parameter
     * @param  [type] $classname [description]
     * @param  [type] $parameter [description]
     * @return [type]            [description]
     */
    public function createMailable($classname, $parameter)
    {
      $temp = \App::getLocale();
      \App::setLocale($this->locale);

      $reflection_class = new \ReflectionClass($classname);
      $mailable         = $reflection_class->newInstanceArgs($parameter);

      \App::setLocale($temp);

      return $mailable;
    }

    /**
     * Populate the mailable with the addresses.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return \Illuminate\Mail\Mailable
     */
    protected function fill(Mailable $mailable)
    {
        return $mailable->to($this->to)
                        ->cc($this->cc)
                        ->bcc($this->bcc)
                        ->locale($this->locale);
    }
}
