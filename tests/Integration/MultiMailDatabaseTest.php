<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests\Integration;

use Illuminate\Support\Facades\View;
use IWasHereFirst2\LaravelMultiMail\Facades\MultiMail;
use IWasHereFirst2\LaravelMultiMail\Models\EmailAccount;
use IWasHereFirst2\LaravelMultiMail\Tests\TestCase;

class MultiMailDatabaseTest extends TestCase
{
    const FROM = 'test@fake.de';

    /** @test */
    public function check_database_email()
    {
        $this->artisan(
            'migrate',
            [
                '--database' => 'testbench',
                '--realpath' => realpath(__DIR__ . '/../../src/Migrations'),
            ]
        )->run();

        dd( \DB::table('email_accounts')->get());

        EmailAccount::create([
            'email' => 'ronyPuh',
            'pass' => 'gomyBore',
        ]);

        dd(EmailAccount::all());


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
