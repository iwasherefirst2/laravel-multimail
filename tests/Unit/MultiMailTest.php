<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests\Unit;

use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;
use IWasHereFirst2\LaravelMultiMail\PendingMail;
use IWasHereFirst2\LaravelMultiMail\Tests\TestCase;

class MultiMailTest extends TestCase
{
    /** @test */
    public function check_if_multi_chaining_works()
    {
        $to     = 'test@bar.com';
        $cc     = 'foo@bar.ur';
        $locale = 'de';
        $from   = 'exampli@foo.cc';
        $bcc    = ['oki@foo.berlin', 'rooky@mooky.de'];

        $classes = [];

        $classes[] = MultiMail::from('dummy');
        $classes[] = MultiMail::cc('dummy');
        $classes[] = MultiMail::bcc('dummy');

        $this->assertContainsOnlyInstancesOf(PendingMail::class, $classes);
    }

    /** @test */
    public function check_if_plugins_deletable()
    {
        MultiMail::clearPlugins();

        $this->assertEquals([], MultiMail::getPlugins());
    }
}
