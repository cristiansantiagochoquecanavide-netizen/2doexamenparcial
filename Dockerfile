# ========================
# Etapa 1: Builder PHP
# ========================
FROM composer:2 AS php-build

WORKDIR /app

COPY . .

RUN mkdir -p bootstrap/cache storage/logs storage/framework \
    && chmod -R 777 bootstrap storage

RUN composer install --no-dev --optimize-autoloader --no-interaction


# ========================
# Etapa 2: Builder de Vite (React)
# ========================
FROM node:18 AS vite-build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install

COPY resources ./resources
COPY vite.config.js .

# build de Vite → genera /public/assets y /public/index.html
RUN npm run build


# ========================
# Etapa 3: Imagen final Apache + PHP
# ========================
FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libpq-dev \
    ghostscript \
    && docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite

# Copiar todo Laravel excepto node_modules
COPY . .

# Copiar assets generados POR VITE y el archivo index.php
COPY public /var/www/html/public

# Copiar vendor del build PHP
COPY --from=php-build /app/vendor /var/www/html/vendor

RUN mkdir -p bootstrap/cache storage \
    && chmod -R 777 bootstrap storage

EXPOSE 8080

CMD ["bash", "start-server.sh"]