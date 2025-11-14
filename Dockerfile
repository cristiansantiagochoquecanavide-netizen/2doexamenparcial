# Etapa 1: Build de Vite
FROM node:18 AS vite-build
WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build



# Etapa 2: PHP + Apache
FROM php:8.2-apache

WORKDIR /var/www/html

# Extensiones
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql

# Activar mod_rewrite
RUN a2enmod rewrite

# Copiar proyecto
COPY . .

# Copiar build de Vite
COPY --from=vite-build /app/public/build ./public/build

# Crear directorios necesarios antes de Composer
RUN mkdir -p bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Script start-server
RUN chmod +x start-server.sh

EXPOSE 10000

CMD ["bash", "start-server.sh"]
