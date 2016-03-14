<?php

namespace VergilLai\UcClient;

use Config;
use Validator;
use Route;
use Illuminate\Support\ServiceProvider;

/**
 * Class ClientProvider
 *
 * @author Vergil <vergil@vip.163.com>
 * @package VergilLai\UcClient
 */
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

        Route::any('api/' . Config::get('ucenter.apifilename'), \VergilLai\UcClient\Controller::class.'@api');

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

        $this->app->bind('\\VergilLai\\UcClient\\Contracts\\UcenterNoteApi', Config::get('ucenter.note_handler'));
    }
}
