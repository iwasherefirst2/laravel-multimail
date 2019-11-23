<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests;

use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;

use IWasHereFirst2\LaravelMultiMail\MultiMailServiceProvider;
use Swift_Events_EventListener;
use Swift_Message;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $emails;

    public function setUp(): void
    {
        parent::setUp();

        MultiMail::registerPlugin(new TestingMailEventListener($this));
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

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}

class TestingMailEventListener implements Swift_Events_EventListener
{
    protected $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function beforeSendPerformed($event)
    {
        $this->test->addEmail($event->getMessage());
    }
}
