FROM php:8.3-cli AS dependencies

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM node:22-alpine AS frontend

WORKDIR /app
RUN apk add --no-cache \
    php83 \
    php83-ctype \
    php83-dom \
    php83-fileinfo \
    php83-mbstring \
    php83-openssl \
    php83-pdo \
    php83-pdo_sqlite \
    php83-session \
    php83-tokenizer \
    php83-xml
RUN ln -s /usr/bin/php83 /usr/bin/php
COPY --from=dependencies /var/www/html/vendor ./vendor
COPY composer.json composer.lock artisan ./
COPY bootstrap ./bootstrap
COPY app ./app
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY vite.config.ts tsconfig.json components.json ./
RUN printf "APP_ENV=production\nAPP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=\nDB_CONNECTION=sqlite\n" > .env \
    && mkdir -p database storage/framework/cache storage/framework/sessions \
    storage/framework/views bootstrap/cache \
    && touch database/database.sqlite
COPY package*.json ./
RUN npm ci
RUN php artisan wayfinder:generate --with-form -vvv
RUN npm run build

FROM php:8.3-cli

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev unzip \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=dependencies /var/www/html/vendor ./vendor
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions \
    storage/framework/views bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 10000

CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
