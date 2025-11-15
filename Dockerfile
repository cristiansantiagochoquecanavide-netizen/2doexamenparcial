# -------------------------
# Etapa 1: PHP deps
# -------------------------
FROM composer:2 AS php-build

WORKDIR /app

# Copiar backend 
COPY backend . 

# Crear directorios requeridos ANTES de composer install
RUN mkdir -p bootstrap/cache storage/logs storage/framework
RUN chmod -R 777 bootstrap storage

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction


# -------------------------
# Etapa 2: Build Vite + React
# -------------------------
FROM node:18 AS vite-build

WORKDIR /app

# Copiar archivos necesarios para NPM desde frontend
COPY frontend/package.json frontend/package-lock.json ./
RUN npm install

# Copiar recursos y configuración de Vite
COPY frontend/resources ./resources
COPY frontend/vite.config.js .
COPY frontend/tailwind.config.js .
COPY frontend/postcss.config.js .

# Generar los assets con Vite
RUN npm run build


# -------------------------
# Etapa 3: Imagen final
# -------------------------
FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libpq-dev \
    ghostscript \
    && docker-php-ext-install pdo pdo_pgsql

# Habilitar módulos necesarios de Apache
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod mime

# Configurar Apache para servir desde /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Copiar backend
COPY backend .

# Copiar frontend public
COPY frontend/public ./public

# Copiar SOLO la carpeta build de Vite generada
COPY --from=vite-build /app/public/build ./public/build

# Copiar vendor generado
COPY --from=php-build /app/vendor ./vendor

RUN mkdir -p bootstrap/cache storage \
    && chmod -R 777 bootstrap storage

EXPOSE 8080

CMD ["bash", "start-server.sh"]
