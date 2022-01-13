<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests\Integration;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\View;
use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;
use IWasHereFirst2\LaravelMultiMail\Tests\TestCase;

use IWasHereFirst2\LaravelMultiMail\Tests\Traits\MailTrap;

class SMPTTest extends TestCase
{
    use MailTrap;

    const FROM = 'smtp@fake.de';

    /** @test */
    public function check_if_smtp_mail_is_sendable()
    {
        $to     = 'test@bar.com';
        $locale = 'de';
        $from   = static::FROM;
        MultiMail::to($to)
                              ->locale($locale)
                              ->from($from)
                              ->send(new TestMail());

        $this->assertTrue($this->messageExists('TestMail Subject'));

        $this->emptyInbox();
    }

    /** @test */
    public function check_smtp_mail_from_name()
    {
        $to     = 'test@bar.com';
        $locale = 'de';
        $from   = static::FROM;
        MultiMail::to($to)
            ->locale($locale)
            ->from($from)
            ->send(new TestMail());

        $message = $this->findMessage('TestMail Subject');

        $this->assertEquals('Adam Nielsen', $message[0]['from_name']);
        $this->emptyInbox();
    }

    /** @test */
    public function check_if_smtp_mail_can_configure_from()
    {
        $to     = 'test@bar.com';
        $locale = 'de';
        $from   = static::FROM;
        MultiMail::to($to)
            ->locale($locale)
            ->from($from, 'Backarony Mockoli')
            ->send(new TestMail());

        $message = $this->findMessage('TestMail Subject');

        $this->assertEquals('Backarony Mockoli', $message[0]['from_name']);
        $this->emptyInbox();
    }

    /** @test */
    public function get_mailer_with_antifloodplugin()
    {
        $mailer = MultiMail::getMailer(static::FROM, 1, 1);

        $this->assertEquals(2, count(MultiMail::getPlugins()));
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__ . '/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        parent::getEnvironmentSetUp($app);

        // Setup default database to use sqlite :memory:
        $app['config']->set('multimail.emails',
        ['smtp@fake.de' => [
            'pass'     => env('MAIL_PASSWORD_SMTP'),
            'username' => env('MAIL_USERNAME_SMTP'),
            'from_name'     => 'Adam Nielsen',
            'provider' => 'smtp',
        ]]);

        $app['config']->set('multimail.provider.smtp', [
            'host'        => env('MAIL_HOST_SMTP'),
            'port'        => env('MAIL_PORT_SMTP'),
            'encryption'  => env('MAIL_ENCRYPTION_SMTP'),
            'driver'      => env('MAIL_DRIVER_SMTP'),
        ]);

        View::addLocation(__DIR__ . '/../Fixtures');
    }
}
