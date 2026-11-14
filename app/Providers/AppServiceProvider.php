<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Api\Convenio;
use App\Observers\ConvenioObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Convenio::observe(ConvenioObserver::class);
    }
    
}
