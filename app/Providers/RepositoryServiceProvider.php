<?php

namespace App\Providers;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
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
