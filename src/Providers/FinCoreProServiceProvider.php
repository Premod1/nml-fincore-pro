<?php

namespace Nml\FinCorePro\Providers;

use Illuminate\Support\ServiceProvider;
use Nml\FinCore\Services\LedgerEngine;
use Nml\FinCorePro\Services\ErpPostingEngine;

class FinCoreProServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/fincore-pro.php',
            'fincore-pro'
        );

        $this->app->singleton('fincore-pro', function ($app) {
            return new ErpPostingEngine($app->make(LedgerEngine::class));
        });
    }

    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'fincore-pro');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/fincore-pro.php' => config_path('fincore-pro.php'),
            ], 'fincore-pro-config');

            $this->publishes([
                __DIR__ . '/../../resources/views' => resource_path('views/vendor/fincore-pro'),
            ], 'fincore-pro-views');
        }
    }
}
