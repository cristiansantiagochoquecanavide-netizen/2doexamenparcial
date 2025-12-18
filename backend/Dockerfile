# Build stage
FROM composer:2.6 as builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --prefer-dist \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# Runtime stage
FROM php:8.2-fpm-alpine

# Instalar extensiones necesarias
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    curl \
    nginx \
    supervisor \
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
RUN echo '[supervisord]\n\
nodaemon=true\n\
logfile=/var/log/supervisord.log\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
autostart=true\n\
autorestart=true\n\
stderr_logfile=/var/log/php-fpm.err.log\n\
stdout_logfile=/var/log/php-fpm.out.log\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true\n\
stderr_logfile=/var/log/nginx.err.log\n\
stdout_logfile=/var/log/nginx.out.log\n\
' > /etc/supervisord.conf

# Script de inicialización
RUN echo '#!/bin/sh\n\
set -e\n\
\n\
echo "🚀 Iniciando aplicación en Docker..."\n\
\n\
if [ ! -f .env ]; then\n\
    echo "Creating .env file..."\n\
    cp .env.example .env || true\n\
fi\n\
\n\
php artisan key:generate --force || true\n\
\n\
echo "📦 Ejecutando migraciones..."\n\
php artisan migrate --force --no-interaction\n\
\n\
echo "👥 Ejecutando seeder..."\n\
php artisan db:seed --class=UsuarioTestSeeder --force --no-interaction || true\n\
\n\
echo "⚙️ Cacheando configuración..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "✅ Inicialización completada"\n\
echo "🌐 Iniciando servidor..."\n\
\n\
exec supervisord -c /etc/supervisord.conf\n\
' > /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]

