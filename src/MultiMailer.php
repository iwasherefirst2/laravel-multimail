<?php

namespace IWasHereFirst2\LaravelMultiMail;

use \Illuminate\Mail\Mailer;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Mail\Mailable;

class MultiMailer
{
    public function __construct(
        private readonly MailSettings $config,
        private readonly MailManager $mailManager
    ) {
    }

    public function from($identifier)
    {
        $this->config->setKey($identifier);

        return $this->mailManager->getMailer($this->config);
    }
}
