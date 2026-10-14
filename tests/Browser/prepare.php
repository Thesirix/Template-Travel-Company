<?php

// Crée la base des tests navigateur si elle n'existe pas (même serveur MySQL que l'application,
// nom donné par DB_DATABASE, fixé par run.mjs à travel_company_e2e).

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$config = config('database.connections.' . config('database.default'));
if (! preg_match('/^[A-Za-z0-9_]+$/', $config['database']) || $config['database'] === 'travel_company') {
    fwrite(STDERR, "Nom de base refusé : {$config['database']} (la base de travail ne doit jamais être utilisée par les tests)\n");
    exit(1);
}

$pdo = new PDO("mysql:host={$config['host']};port={$config['port']}", $config['username'], $config['password']);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
