<?php

namespace App\Domains\Posts\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Posts\Repositories\PostRepositoryInterface;
use App\Domains\Posts\Repositories\EloquentPostRepository;
use App\Domains\Posts\Services\PostService;

class PostsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PostRepositoryInterface::class,
            EloquentPostRepository::class
        );

        $this->app->singleton(PostService::class, function ($app) {
            return new PostService(
                $app->make(PostRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/Posts.php');
    }
}