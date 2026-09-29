# =========================
# 1. Build frontend
# =========================
FROM node:24-bookworm AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build


# =========================
# 2. Laravel / PHP
# =========================
FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        bcmath \
        intl \
        zip \
        gd \
    && rm -rf /var/lib/apt/lists/*


# =========================
# 3. Composer
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html


# =========================
# 4. Copy Laravel project
# =========================
COPY . .


# =========================
# 5. Install Laravel dependencies
# =========================
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist


# =========================
# 6. Copy Vite build
# =========================
COPY --from=frontend /app/public/build ./public/build


# =========================
# 7. Permissions
# =========================
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache


# =========================
# 8. Nginx
# =========================
COPY docker/nginx.conf /etc/nginx/sites-available/default

COPY docker/start.sh /start.sh

RUN chmod +x /start.sh


EXPOSE 10000

CMD ["/start.sh"]
