<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        parent::boot();

        $this->routes(function () {
            // API routes (/api/*)
            Route::prefix('api')
                 ->middleware('api')
                 ->group(base_path('routes/api.php'));

            // Web routes (/web, /)
            Route::middleware('web')
                 ->group(base_path('routes/web.php'));
        });
    }

    public const HOME = '/redirect-based-on-role';

}
