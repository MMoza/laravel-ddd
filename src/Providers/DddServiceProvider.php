<?php

namespace LaravelDdd\Starter\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelDdd\Starter\Commands\DddInstallCommand;
use LaravelDdd\Starter\Commands\DddMakeModuleCommand;
use LaravelDdd\Starter\Commands\DddMakeEntityCommand;
use LaravelDdd\Starter\Commands\DddMakeServiceCommand;
use LaravelDdd\Starter\Commands\DddMakeRepositoryCommand;
use LaravelDdd\Starter\Commands\DddMakeValueObjectCommand;
use LaravelDdd\Starter\Commands\DddMakeControllerCommand;
use LaravelDdd\Starter\Commands\DddMakeRequestCommand;
use LaravelDdd\Starter\Commands\DddMakeResourceCommand;
use LaravelDdd\Starter\Commands\DddMakeRoutesCommand;
use LaravelDdd\Starter\Commands\DddTestCommand;
use LaravelDdd\Starter\Commands\DddListCommand;

class DddServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ddd.php', 'ddd');
    }

    public function boot(): void
    {
        $this->publishConfig();
        $this->registerCommands();
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/ddd.php' => config_path('ddd.php'),
        ], 'ddd-config');
    }

    protected function registerCommands(): void
    {
        $this->commands([
            DddInstallCommand::class,
            DddMakeModuleCommand::class,
            DddMakeEntityCommand::class,
            DddMakeServiceCommand::class,
            DddMakeRepositoryCommand::class,
            DddMakeValueObjectCommand::class,
            DddMakeControllerCommand::class,
            DddMakeRequestCommand::class,
            DddMakeResourceCommand::class,
            DddMakeRoutesCommand::class,
            DddTestCommand::class,
            DddListCommand::class,
        ]);
    }
}