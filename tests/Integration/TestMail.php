<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests\Integration;

use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject = 'TestMail Subject';
        return $this->view('view');
    }
}
