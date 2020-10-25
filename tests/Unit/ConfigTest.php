<?php

namespace IWasHereFirst2\LaravelMultiMail\Tests\Unit;

use IWasHereFirst2\LaravelMultiMail\FileConfigMailSettings;
use IWasHereFirst2\LaravelMultiMail\Exceptions\EmailNotInConfigException;
use IWasHereFirst2\LaravelMultiMail\Exceptions\InvalidConfigKeyException;
use IWasHereFirst2\LaravelMultiMail\Exceptions\NoDefaultException;
use IWasHereFirst2\LaravelMultiMail\Tests\TestCase;

class ConfigTest extends TestCase
{
    /** @test */
    public function create_config_with_invalid_key()
    {
        $this->expectException(EmailNotInConfigException::class);

        (new FileConfigMailSettings())->initialize('unknown key');
    }

    /** @test */
    public function create_config_with_invalid_array_key()
    {
        $this->expectException(InvalidConfigKeyException::class);

        (new FileConfigMailSettings())->initialize(['unknown key']);
    }

    /** @test */
    public function create_config_with_valid_key()
    {
        $config = (new FileConfigMailSettings())->initialize(['name' => 'Adam', 'email' => 'test@fake.de']);

        $this->assertEquals('test@fake.de', $config->getFromEmail());
    }

    /** @test */
    /*
    public function test_non_existing_mail()
    {
        $this->expectException(\Exception::class);
        $config = new Config(['name' => 'Adam', 'email' => 'test@faki.de']);
    }*/

    /** @test */
    public function get_reply_name()
    {
        $config = (new FileConfigMailSettings())->initialize(['name' => 'Adam', 'email' => 'test@fake.de']);

        $this->assertEquals('max', $config->getReplyName());
    }

    /** @test */
    public function load_invalid_default()
    {
        $this->expectException(NoDefaultException::class);
        $config = (new FileConfigMailSettings())->initialize(['name' => 'Adam', 'email' => 'test@empty.de']);
    }

    /** @test */
    public function load_valid_default()
    {
        app()['config']->set('multimail.emails.default',
             [
                 'pass'          => 'fakepass',
                 'username'      => 'fakeusername',
                 'from'          => 'Who knows',
                 'reply_to_mail' => 'bs@web.de',
                 'reply_to_name' => 'max',
             ]);

        app()['config']->set('multimail.provider.default', [
            'driver'   => 'log',
        ]);

        $config = (new FileConfigMailSettings())->initialize(['name' => 'Adam', 'email' => 'test@empty.de']);

        $this->assertNotNull($config);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('multimail.emails',
            ['test@fake.de' => [
                'pass'          => 'fakepass',
                'username'      => 'fakeusername',
                'from'          => 'Who knows',
                'reply_to_mail' => 'bs@web.de',
                'reply_to_name' => 'max',
            ],
                'test@empty.de' => [
                    'pass'          => '',
                    'username'      => '',
                    'from'          => 'Who knows',
                    'reply_to_mail' => 'bs@web.de',
                    'reply_to_name' => 'max',
                ], ]);
    }
}
