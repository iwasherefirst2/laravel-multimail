<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests;

use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;

use IWasHereFirst2\LaravelMultiMail\MultiMailServiceProvider;
use Mail;
use Swift_Events_EventListener;
use Swift_Message;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $emails;

    public function setUp(): void
    {
        parent::setUp();

        MultiMail::registerPlugin(new TestingMailEventListener($this));
        Mail::getSwiftMailer()
                ->registerPlugin(new TestingMailEventListener($this));
    }

    public function addEmail(Swift_Message $email)
    {
        $this->emails[] = $email;
    }

    /**
     * add the package provider
     *
     * @param $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [MultiMailServiceProvider::class];
    }
}

class TestingMailEventListener implements Swift_Events_EventListener
{
    protected $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function getDebugInfo()
    {
        return 'This is the Custom Test Case Event Plugin';
    }

    public function beforeSendPerformed($event)
    {
        $this->test->addEmail($event->getMessage());
    }
}
