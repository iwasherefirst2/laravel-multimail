<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests\Unit;

use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;
use IWasHereFirst2\LaravelMultiMail\Tests\TestCase;

class PendingMailTest extends TestCase
{
    /** @test */
    public function check_if_object_chaining_works()
    {
        $to     = 'test@bar.com';
        $cc     = 'foo@bar.ur';
        $locale = 'de';
        $from   = 'exampli@foo.cc';
        $bcc    = ['oki@foo.berlin', 'rooky@mooky.de'];

        $pendingMail = MultiMail::to($to)
                              ->cc($cc)
                              ->locale($locale)
                              ->from($from)
                              ->bcc($bcc);

        $pendingMail2 = MultiMail::locale($locale)
                              ->from($from)
                              ->to($to)
                              ->cc($cc)
                              ->bcc($bcc);

        $this->assertEquals($pendingMail, $pendingMail2);
    }
}
