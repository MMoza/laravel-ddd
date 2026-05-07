<?php

namespace App\Domains\Users\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Users\Repositories\UserRepositoryInterface;
use App\Domains\Users\Repositories\EloquentUserRepository;
use App\Domains\Users\Services\UserService;

class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->singleton(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/Users.php');
    }
}