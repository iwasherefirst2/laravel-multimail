<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests\Unit;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\View;
use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;
use IWasHereFirst2\LaravelMultiMail\Tests\TestCase;
use Swift_Events_EventListener;
use Swift_Message;

class MultiMailTest extends TestCase
{
    const FROM = 'test@fake.de';

    protected $emails;

    public function setUp(): void
    {
        parent::setUp();

        MultiMail::registerPlugin(new TestingMailEventListener($this));
    }

    /** @test */
    public function check_if_mail_is_sendable()
    {
        $to     = 'test@bar.com';
        $cc     = 'foo@bar.ur';
        $locale = 'de';
        $from   = static::FROM;
        $bcc    = ['oki@foo.berlin', 'rooky@mooky.de'];
        $pendingMail = MultiMail::to($to)
                              ->cc($cc)
                              ->locale($locale)
                              ->from($from)
                              ->bcc($bcc)
                              ->send(new TestMail());

        $this->assertEquals(1, count($this->emails));
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
        $app['config']->set('multimail.emails',
        ['test@fake.de' => [
            'pass'     => 'fakepass',
            'username' => 'fakeusername',
            'from'     => 'Who knows',
        ]]);
        $app['config']->set('multimail.provider.default', [
            'driver'   => 'log',
        ]);

        View::addLocation(__DIR__ . '/Fixtures');
    }

    public function addEmail(Swift_Message $email)
    {
        $this->emails[] = $email;
    }
}

class TestMail extends Mailable
{
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('view');
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
