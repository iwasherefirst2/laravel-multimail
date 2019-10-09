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
        $this->app->bind('iwasherefirst2-laravelmultimail', function () {
            return new MultiMailer();
        });

        $this->publishes([
            dirname(__DIR__) . '/publishable/config/multimail.php' => config_path('multimail.php'),
        ]);
    }
}
