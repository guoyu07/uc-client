<?php

namespace VergilLai\UcClient;

<<<<<<< HEAD
use Validator;
use Illuminate\Support\ServiceProvider;


=======
use Illuminate\Support\ServiceProvider;

>>>>>>> 87ebeaa0a56d3b7371e43504e044a354567ef888
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
<<<<<<< HEAD

        Validator::extend('uc_username', '\\VergilLai\\UcClient\\Validator@usernameValidate');
        Validator::extend('uc_email', '\\VergilLai\\UcClient\\Validator@emailValidate');
=======
>>>>>>> 87ebeaa0a56d3b7371e43504e044a354567ef888
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
