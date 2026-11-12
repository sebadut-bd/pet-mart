<?php

namespace App\Providers;

use App\Registers\ResourceRegister;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(\App\Services\Sms\SmsSender::class, \App\Services\Sms\LogSmsSender::class);
    }

    public function boot()
    {
        Paginator::useBootstrap();
        $registrar = new ResourceRegister($this->app['router']);

        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function () use ($registrar) {
            return $registrar;
        });
    }
}
