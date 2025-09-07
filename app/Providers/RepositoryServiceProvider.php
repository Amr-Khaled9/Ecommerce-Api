<?php

namespace App\Providers;

use App\Repository\RepositoryFunction\CategoryRepository;
use App\Repository\RepositoryFunction\ProductRepository;
use App\Repository\RepositoryInterface\CategoryRepositoryInterface;
use App\Repository\RepositoryInterface\ProductRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
             ProductRepositoryInterface::class,
             ProductRepository::class
        );
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
