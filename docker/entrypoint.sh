#!/bin/sh
# Démarrage du conteneur : configuration, base de démonstration, serveur web.
set -e
cd /app

# Fichier .env : modèle du dépôt, les variables du docker-compose.yml priment
[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force --no-interaction
grep -q '^JWT_SECRET=.\+' .env || php artisan jwt:secret --force --no-interaction

# Attend MySQL, crée la base si besoin et renvoie son nombre de tables (via PDO, comme l'application)
db() {
    php -r '
        $dsn = "mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT");
        $name = getenv("DB_DATABASE");
        for ($i = 0; $i < 90; $i++) {
            try { $pdo = new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD")); break; } catch (PDOException $e) { sleep(2); }
        }
        if (empty($pdo)) { fwrite(STDERR, "MySQL ne répond pas\n"); exit(1); }
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $count = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?");
        $count->execute([$name]);
        echo $count->fetchColumn();
    '
}

echo "Attente de MySQL..."
TABLES=$(db)

# Données de démonstration : au premier lancement, ou à chaque démarrage si DEMO_RESET=true
if [ "$TABLES" = "0" ] || [ "$DEMO_RESET" = "true" ]; then
    echo "Création de la base et des données de démonstration (1 à 2 minutes)..."
    php artisan migrate:fresh --seed --force --no-interaction
else
    php artisan migrate --force --no-interaction
fi

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan optimize:clear >/dev/null

echo ""
echo "  Template Travel Company est prêt : http://localhost:${APP_PORT:-8000}"
echo "  admin@travel-company.test / super.agent@travel-company.test — mot de passe Demo1234!"
echo ""
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
