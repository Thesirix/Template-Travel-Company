# Template Travel Company — image de démonstration (PHP 8.4 + extensions + dépendances Composer)
# Lancement : docker compose up   →   http://localhost:8000
FROM php:8.4-cli

# Extensions PHP nécessaires (MySQL, images, Excel, PDF, cache d'opcodes)
COPY --from=mlocati/php-extension-installer:2.7 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql gd zip intl bcmath exif opcache \
    && apt-get update && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

# Dépendances d'abord (couche mise en cache tant que composer.lock ne change pas)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod +x docker/entrypoint.sh

ENV PHP_CLI_SERVER_WORKERS=8
EXPOSE 8000
ENTRYPOINT ["docker/entrypoint.sh"]
