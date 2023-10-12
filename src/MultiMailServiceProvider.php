<?php

namespace IWasHereFirst2\LaravelMultiMail;

use Illuminate\Support\ServiceProvider;

class MultiMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(MultiMailer::class, function ($app) {
            $defaultLaravelmailDriver = new DefaultLaravelMailDriver();

            if (config()->has('multimail.mail_settings_class')) {
                $configClass = config('multimail.mail_settings_class');
                $config = new $configClass($defaultLaravelmailDriver);
            } else {
                $config =  new FileConfigMailSettings($defaultLaravelmailDriver);
            }

            $mailManager = new MailManager($app);

            return new MultiMailer($config, $mailManager);
        });

        $this->publishes([
            dirname(__DIR__) . '/publishable/config/multimail.php' => config_path('multimail.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/Migrations/' => database_path('migrations'),
        ], 'migrations');
    }
}
