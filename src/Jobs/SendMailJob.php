<?php

namespace IWasHereFirst2\LaravelMultiMail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailer_name;
    protected $mailable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mailer_name, $mailable)
    {
        $this->mailer_name       = $mailer_name;
        $this->mailable          = $mailable;
    }

    /**
     * Send Email out. This is a queue workaround
     *
     * @return void
     */
    public function handle()
    {
        MultiMail::from($this->mailer_name)->send($this->mailable);
    }
}
