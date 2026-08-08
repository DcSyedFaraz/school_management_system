<?php

namespace App\Providers;

use App\Services\GradingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GradingService::class, function ($app) {
            return new GradingService(
                $app['config']->get('grading', []),
                $app['config']->get('subjects', [])
            );
        });

        // Accessor used by App\Facades\Grading.
        $this->app->alias(GradingService::class, 'grading');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
