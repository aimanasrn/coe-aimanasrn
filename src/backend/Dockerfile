FROM php:8.5-cli-alpine

RUN apk add --no-cache git unzip icu-dev libzip-dev oniguruma-dev libpq-dev \
    && docker-php-ext-install -j"$(nproc)" bcmath intl mbstring pcntl pdo_pgsql zip \
    && addgroup -g 1000 app \
    && adduser -D -u 1000 -G app app

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
COPY php-upload.ini /usr/local/etc/php/conf.d/uploads.ini
RUN chown app:app /app
USER app
COPY --chown=app:app composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader

COPY --chown=app:app . .
RUN composer dump-autoload --no-interaction

EXPOSE 8000
ENTRYPOINT ["sh", "/app/docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
