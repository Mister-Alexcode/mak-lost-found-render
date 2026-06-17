# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 — build front-end assets (Vite / Tailwind / Alpine)
# ---------------------------------------------------------------------------
FROM node:20-bullseye-slim AS assets

WORKDIR /app

# Install JS deps using the lockfile for reproducible builds.
COPY package.json package-lock.json ./
RUN npm ci

# Tailwind scans blade/app files for class names, and Vite needs the configs,
# so copy the whole project before building.
COPY . .
RUN npm run build


# ---------------------------------------------------------------------------
# Stage 2 — PHP runtime (Apache serving Laravel's public/ directory)
# ---------------------------------------------------------------------------
FROM php:8.2-apache AS app

# System libraries needed to build the PHP extensions below + composer tooling.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd zip bcmath exif \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Composer (copied from the official image).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first (better layer caching). No dev deps in production.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Application source.
COPY . .

# Built front-end assets from stage 1.
COPY --from=assets /app/public/build ./public/build

# Finish composer (now that all source is present) and optimize the autoloader.
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi || true

# Laravel needs these writable by the web server user.
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Apache vhost + ports template (port is substituted at runtime from $PORT).
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Render injects $PORT (default 10000). Documented for clarity.
ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["entrypoint"]
