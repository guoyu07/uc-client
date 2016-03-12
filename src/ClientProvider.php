<?php

namespace VergilLai\UcClient;

use Validator;
use Illuminate\Support\ServiceProvider;

class ClientProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/ucenter.php' => config_path('ucenter.php')
        ]);

        Validator::extend('uc_username', '\\VergilLai\\UcClient\\Validator@usernameValidate');
        Validator::extend('uc_email', '\\VergilLai\\UcClient\\Validator@emailValidate');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('uc-client', function($app) {
            return new Client();
        });
    }
}
