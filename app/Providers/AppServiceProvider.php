<?php

namespace App\Providers;

use App\Support\Branding;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Branding::class);
    }

    public function boot()
    {
        // L'interface utilise le balisage Bootstrap 4
        Paginator::useBootstrapFour();

        View::composer('*', function ($view) {
            $view->with('brand', $this->app->make(Branding::class));
        });

        // Windows : `php artisan serve` ne transmet pas TEMP/TMP au serveur PHP,
        // qui ne peut alors plus recevoir de fichiers (logo, passeports, PDF...).
        if ($this->app->runningInConsole()) {
            \Illuminate\Foundation\Console\ServeCommand::$passthroughVariables[] = 'TEMP';
            \Illuminate\Foundation\Console\ServeCommand::$passthroughVariables[] = 'TMP';
        }

        // Le nom choisi dans les paramètres remplace celui du .env (mails, titres...)
        if (! $this->app->runningInConsole()) {
            config(['agency.name' => $this->app->make(Branding::class)->name()]);
        }
    }
}
