<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GroupService;
use App\Services\TagsService;
use App\Services\ActivityService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GroupService::class, function ($app) {
            return new GroupService();
        });

        $this->app->singleton(TagsService::class, function ($app) {
            return new TagsService(); 
        });

        $this->app->singleton(ActivityService::class, function ($app) {
            return new ActivityService(); 
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
