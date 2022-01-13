<?php

namespace IWasHereFirst2\LaravelMultiMail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;

class PendingMail
{
    /**
     * The string of the mailer the user wishes to send from, or an array ['name' => .., 'email' => ..., 'reply_to']
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
     * Overrwrites name of from mail cofnig
     *
     * @var ?string
     */
    protected $fromName = null;

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
    public function from($mailerKey, $fromName = null)
    {
        $this->fromName = $fromName;
        $this->fromMailer = $mailerKey;

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

        return $this->sendNow($this->fill($mailable));
    }

    /**
     * Send a mailable message immediately.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function sendNow(Mailable $mailable)
    {
        $mailer = $this->fromMailer ?? optional($mailable)->fromMailer;

        return MultiMail::sendMail($this->fill($mailable), $mailer, $this->fromName);
    }

    /**
     * Push the given mailable onto the queue.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return mixed
     */
    public function queue(Mailable $mailable)
    {
        $mailer = $this->fromMailer ?? optional($mailable)->fromMailer;

        return MultiMail::queueMail($this->fill($mailable), $mailer, $this->fromName);
    }

    /**
     * Populate the mailable with the addresses.
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return \Illuminate\Mail\Mailable
     */
    protected function fill(Mailable $mailable)
    {
        if (!empty($this->locale)) {
            $mailable->locale($this->locale);
        }

        return $mailable->to($this->to)
                        ->cc($this->cc)
                        ->bcc($this->bcc);
    }
}
