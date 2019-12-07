<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests\Integration;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\View;
use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;
use IWasHereFirst2\LaravelMultiMail\Tests\TestCase;

class MultiMailTest extends TestCase
{
    const FROM = 'test@fake.de';

    /** @test */
    public function check_if_mail_is_sendable()
    {
        $to     = 'test@bar.com';
        $cc     = 'foo@bar.ur';
        $locale = 'de';
        $from   = static::FROM;
        $bcc    = ['oki@foo.berlin', 'rooky@mooky.de'];

        MultiMail::to($to)
                    ->cc($cc)
                    ->locale($locale)
                    ->from($from)
                    ->bcc($bcc)
                    ->send(new TestMail());

        $this->assertNotNull($this->emails);
        $this->assertEquals(1, count($this->emails));
    }

    /** @test */
    public function check_if_from_name_works()
    {
        MultiMail::clearPlugins();

        MultiMail::to('test@bar.com')->from(['name' => 'Adam', 'email' => 'test@fake.de'])->send(new TestMail());

        $this->assertEquals([], MultiMail::getPlugins());
    }

    /** @test */
    public function send_mail_directly()
    {
        MultiMail::send(new TestMailIncludingFrom());

        $this->assertNotNull($this->emails);
        $this->assertEquals(1, count($this->emails));
    }

    /** @test */
    public function send_mail_implementing_queue()
    {
        MultiMail::send(new QueueTestMailIncludingFrom());

        $this->assertNotNull($this->emails);
        $this->assertEquals(1, count($this->emails));
    }

    /** @test */
    public function send_mail_through_queue()
    {
        MultiMail::queue(new TestMailIncludingFrom());

        $this->assertNotNull($this->emails);
        $this->assertEquals(1, count($this->emails));
    }

    /** @test */
    public function send_mail_through_default()
    {
        MultiMail::send(new TestMail());

        $this->assertNotNull($this->emails);
        $this->assertEquals(1, count($this->emails));
    }

    /** @test */
    public function send_mail_through_queue_default()
    {
        MultiMail::queue(new TestMail());

        $this->assertNotNull($this->emails);
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
            'pass'          => 'fakepass',
            'username'      => 'fakeusername',
            'from'          => 'Who knows',
            'reply_to_mail' => 'bs@web.de',
        ]]);

        $app['config']->set('multimail.provider.default', [
            'driver'   => 'log',
        ]);

        $app['config']->set('mail.driver', 'log');

        View::addLocation(__DIR__ . '/../Fixtures');
    }
}

class TestMailIncludingFrom extends Mailable
{
    public function __construct()
    {
        $this->fromMailer    = MultiMailTest::FROM;
    }

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

class QueueTestMailIncludingFrom extends Mailable implements ShouldQueue
{
    public function __construct()
    {
        $this->fromMailer    = MultiMailTest::FROM;
    }

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
