<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Pages publiques (routes/web.php), espace agent (routes/agent.php) et administration (routes/admin.php) :
     * session, CSRF et cookies (groupe « web »).
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')->group(function () {
            require base_path('routes/web.php');
            require base_path('routes/agent.php');
            require base_path('routes/admin.php');
        });
    }

    /**
     * API du site public (routes/api.php) : JSON, authentification JWT des clients.
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('client')
             ->group(base_path('routes/api.php'));
    }
}
