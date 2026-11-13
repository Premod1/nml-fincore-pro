<?php

namespace Nml\FinCorePro\Tests;

use Nml\FinCore\Providers\FinCoreServiceProvider;
use Nml\FinCore\Services\ChartOfAccountsInitializer;
use Nml\FinCorePro\Providers\FinCoreProServiceProvider;
use Nml\FinCorePro\Services\ErpPostingEngine;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FinCoreServiceProvider::class,
            FinCoreProServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:2fl+Ktvkfl+Fuz4Qp/A75G2RTiWVA/ZoKZvp6fiiM10=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('fincore-pro.mode', 'standalone');
        $app['config']->set('fincore-pro.gemini.api_key', 'test-gemini-key');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../vendor/nml/fincore/src/Database/Migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();

        ChartOfAccountsInitializer::initialize();
        $this->app->make(ErpPostingEngine::class)->syncDefaultRules();
    }
}
