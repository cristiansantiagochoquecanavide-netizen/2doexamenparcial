# Etapa 1: Builder de PHP
FROM composer:2 AS php-build

WORKDIR /app

# Copiar TODO el proyecto (incluye artisan)
COPY . .

# Crear directorios requeridos ANTES de composer
RUN mkdir -p bootstrap/cache storage/logs storage/framework \
    && chmod -R 777 bootstrap storage

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction


# Etapa 2: Builder de Vite
FROM node:18 AS vite-build

WORKDIR /app

COPY . .

RUN npm install
RUN npm run build


# Etapa 3: Imagen final
FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libpq-dev \
    ghostscript \
    && docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite

# Copiar código del proyecto
COPY . .

# Copiar archivos construidos
COPY --from=vite-build /app/public/build ./public/build
COPY --from=php-build /app/vendor ./vendor

# Permisos
RUN mkdir -p bootstrap/cache storage \
    && chmod -R 777 bootstrap storage

EXPOSE 8080

CMD ["bash", "start-server.sh"]
