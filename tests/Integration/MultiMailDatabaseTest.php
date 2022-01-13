<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests\Integration;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\View;
use IWasHereFirst2\LaravelMultiMail\DatabaseConfigMailSettings;
use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;
use IWasHereFirst2\LaravelMultiMail\Models\EmailAccount;
use IWasHereFirst2\LaravelMultiMail\Models\EmailProvider;
use IWasHereFirst2\LaravelMultiMail\Tests\TestCase;
use IWasHereFirst2\LaravelMultiMail\Tests\Traits\MailTrap;

class MultiMailDatabaseTest extends TestCase
{
    use MailTrap;
    const FROM = 'ronyPuh@foo.com';

    /** @test */
    public function check_database_email()
    {
        $this->loadMigrationsFrom(realpath(__DIR__ . '/../../src/Migrations'));

        $this->artisan('migrate',['--database' => 'testbench'])
            ->run();

        $provider = EmailProvider::create([
            'host'        => env('MAIL_HOST_SMTP'),
            'port'        => env('MAIL_PORT_SMTP'),
            'encryption'  => env('MAIL_ENCRYPTION_SMTP'),
            'driver'      => env('MAIL_DRIVER_SMTP'),
        ]);

        EmailAccount::create([
            'email' => static::FROM,
            'pass'     => env('MAIL_PASSWORD_SMTP'),
            'username' => env('MAIL_USERNAME_SMTP'),
            'from_name'     => 'Rayn Roogen',
            'provider_id' => $provider->id
        ]);

        $to     = 'foo-fighter@foo.com';
        $locale = 'de';
        $from   = static::FROM;
        $bcc    = ['oki@foo.berlin', 'rooky@mooky.de'];

        MultiMail::to($to)
            ->locale($locale)
            ->from($from)
            ->bcc($bcc)
            ->send(new TestMail());

        $message = $this->findMessage('TestMail Subject');

        $this->assertEquals('Rayn Roogen', $message[0]['from_name']);
        $this->emptyInbox();
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

        $app->useEnvironmentPath(__DIR__ . '/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        $app['config']->set('multimail.mail_settings_class',
            DatabaseConfigMailSettings::class);

        $app['config']->set('multimail.provider.default', [
            'driver'   => 'log',
        ]);

        $app['config']->set('mail.driver', 'log');

        View::addLocation(__DIR__ . '/../Fixtures');
    }
}
