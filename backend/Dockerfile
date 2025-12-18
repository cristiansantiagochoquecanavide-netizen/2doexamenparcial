# Build stage - Instalar dependencias
FROM composer:2.6 as builder

WORKDIR /app

# Copiar composer files
COPY composer.json composer.lock ./

# Instalar sin scripts (evita error de artisan)
RUN composer install \
    --prefer-dist \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --no-scripts

# Runtime stage
FROM php:8.2-fpm-alpine

# Instalar extensiones necesarias
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    zip \
    curl \
    nginx \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    mbstring \
    zip \
    bcmath \
    gd \
    && rm -rf /var/cache/apk/*

# Configurar PHP
RUN echo 'memory_limit = 256M' > /usr/local/etc/php/conf.d/memory.ini

WORKDIR /app

# Copiar dependencias desde builder
COPY --from=builder /app/vendor ./vendor

# Copiar aplicación
COPY . .

# Crear directorios necesarios
RUN mkdir -p storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copiar configuración de Nginx
COPY nginx.conf.docker /etc/nginx/http.d/default.conf

# Copiar supervisord config
RUN cat > /etc/supervisord.conf << 'EOF'
[supervisord]
nodaemon=true
logfile=/var/log/supervisord.log

[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stderr_logfile=/var/log/php-fpm.err.log
stdout_logfile=/var/log/php-fpm.out.log

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/var/log/nginx.err.log
stdout_logfile=/var/log/nginx.out.log
EOF


# Script de inicialización
RUN cat > /entrypoint.sh << 'EOF'
#!/bin/sh
set -e

echo "🚀 Iniciando aplicación en Docker..."

if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env || true
fi

php artisan key:generate --force || true

echo "📦 Ejecutando migraciones..."
php artisan migrate --force --no-interaction

echo "👥 Ejecutando seeder..."
php artisan db:seed --class=UsuarioTestSeeder --force --no-interaction || true

echo "⚙️ Cacheando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Inicialización completada"
echo "🌐 Iniciando servidor..."

exec supervisord -c /etc/supervisord.conf
EOF

RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]

