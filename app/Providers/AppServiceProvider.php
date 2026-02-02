<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use App\Http\Middleware\CheckPermission;
use App\Services\SmartSMSService;


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
         // Ensure the 'permission' alias always resolves, including on terminate()
         $this->app['router']->aliasMiddleware(
            'permission',
            CheckPermission::class
            
        );

        $this->app->singleton(SmartSMSService::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

}
}
