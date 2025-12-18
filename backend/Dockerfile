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

# Copiar .env.example PRIMERO para que esté disponible
COPY .env.example .env.example

# Copiar aplicación
COPY . .

# Limpiar cachés de bootstrap (contienen referencias a proveedores que pueden no estar disponibles)
RUN rm -rf bootstrap/cache/* \
    && mkdir -p storage/logs bootstrap/cache \
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
    echo "Creating .env file from .env.example..."
    cp .env.example .env || true
fi

# Actualizar variables de entorno desde Render en el .env
# Esto asegura que las variables configuradas en Render se usen en la aplicación
echo "📝 Actualizando variables de entorno..."

# Función para actualizar o agregar una variable en .env
update_env() {
    KEY=$1
    VALUE=$2
    if grep -q "^$KEY=" .env; then
        sed -i "s|^$KEY=.*|$KEY=$VALUE|" .env
    else
        echo "$KEY=$VALUE" >> .env
    fi
}

# Actualizar APP_KEY desde variable de entorno
if [ -n "$APP_KEY" ]; then
    update_env "APP_KEY" "$APP_KEY"
    echo "✅ APP_KEY configurado desde Render"
fi

# Actualizar variables de base de datos
[ -n "$DB_HOST" ] && update_env "DB_HOST" "$DB_HOST"
[ -n "$DB_PORT" ] && update_env "DB_PORT" "$DB_PORT"
[ -n "$DB_DATABASE" ] && update_env "DB_DATABASE" "$DB_DATABASE"
[ -n "$DB_USERNAME" ] && update_env "DB_USERNAME" "$DB_USERNAME"
[ -n "$DB_PASSWORD" ] && update_env "DB_PASSWORD" "$DB_PASSWORD"

# Si APP_KEY sigue vacío, generar uno nuevo
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "🔑 Generando APP_KEY..."
    php artisan key:generate --force 2>/dev/null || echo "⚠️ No se pudo generar APP_KEY"
fi

# Limpiar cachés antiguos
rm -rf bootstrap/cache/*.php storage/framework/cache/data/* 2>/dev/null || true

# Caching rápido
echo "⚙️ Cacheando configuración..."
php artisan config:cache 2>/dev/null || echo "⚠️ Config cache falló"
php artisan route:cache 2>/dev/null || echo "⚠️ Route cache falló"
php artisan view:cache 2>/dev/null || echo "⚠️ View cache falló"

echo "✅ Inicialización rápida completada"
echo "🌐 Iniciando servidor..."

# Ejecutar migraciones y seeder en background (no bloquea el servidor)
(
    sleep 5  # Esperar a que el servidor esté listo
    echo ""
    echo "📦 Ejecutando migraciones en background..."
    php artisan migrate --force --no-interaction 2>/dev/null || echo "⚠️ Las migraciones fallaron"
    
    echo "👥 Ejecutando seeder en background..."
    php artisan db:seed --class=UsuarioTestSeeder --force --no-interaction 2>/dev/null || echo "⚠️ El seeder falló"
    
    echo "✅ Migraciones y seeder completadas"
) &

exec supervisord -c /etc/supervisord.conf

exec supervisord -c /etc/supervisord.conf
EOF

RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]

