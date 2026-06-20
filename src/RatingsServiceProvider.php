<?php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings;

use AndyDefer\LaravelRatings\Repositories\RatingRepository;
use AndyDefer\LaravelRatings\Services\RatingService;
use Illuminate\Support\ServiceProvider;

final class RatingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RatingRepository::class);
        $this->app->singleton(RatingService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/migrations');
        }

        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations'),
        ], 'Ratings-migrations');
    }
}
