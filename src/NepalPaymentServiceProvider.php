<?php

namespace Sagartimilsina\NepalPayment;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NepalPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nepal-payment.php', 'nepal-payment');

        $this->app->singleton(NepalPaymentManager::class, function ($app) {
            return new NepalPaymentManager($app->make('config')->get('nepal-payment'));
        });

        $this->app->alias(NepalPaymentManager::class, 'nepal-payment');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nepal-payment');

        if (config('nepal-payment.demo_route_enabled', false)) {
            Route::middleware('web')->group(__DIR__.'/../routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/nepal-payment.php' => config_path('nepal-payment.php'),
            ], 'nepal-payment-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/nepal-payment'),
            ], 'nepal-payment-views');
        }
    }
}