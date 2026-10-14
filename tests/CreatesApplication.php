<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Fichiers écrits pendant les tests (PDF des données de démo, envois) : dans un dossier à part,
        // jamais dans storage/app/public de l'application
        $app['config']->set('filesystems.disks.public.root', $app->storagePath('framework/testing/public'));

        return $app;
    }
}
