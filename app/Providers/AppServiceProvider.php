<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use CodeDredd\Soap\SoapFactory;
use App\Soap\CustomSoapClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(SoapFactory::class, function ($factory) {
            // Bind factory to use our CustomSoapClient
            $factory->macro('client', function () use ($factory) {
                return new CustomSoapClient($factory);
            });

            return $factory;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ScaffoldSpgSoap::class,
            ]);
        }
    }
}
