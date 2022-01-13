<?php

namespace IWasHereFirst2\LaravelMultiMail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailer_name;
    protected $mailable;
    protected $fromName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mailer_name, $mailable, $fromName)
    {
        $this->mailer_name       = $mailer_name;
        $this->mailable          = $mailable;
        $this->fromName          = $fromName;
    }

    /**
     * Send Email out. This is a queue workaround
     *
     * @return void
     */
    public function handle()
    {
        MultiMail::sendMail($this->mailable, $this->mailer_name, $this->fromName);
    }
}
