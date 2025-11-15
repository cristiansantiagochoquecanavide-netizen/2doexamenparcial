#!/bin/bash

echo "=========================================="
echo "Iniciando Laravel en Render con Apache"
echo "=========================================="

# Mostrar variables importantes (debug)
echo "APP_ENV=$APP_ENV"
echo "APP_DEBUG=$APP_DEBUG"
echo "DB_HOST=$DB_HOST"
echo "PORT=$PORT"

# Limpiar caches viejas
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Compilar caches nuevas
php artisan config:cache
php artisan route:cache
php artisan view:cache || true
php artisan optimize

# Ejecutar migraciones sin bloquear si falla
echo "Ejecutando migraciones..."
php artisan migrate --force || true

# Configurar Apache para escuchar en el puerto especificado por Render
echo "Configurando Apache en puerto $PORT..."
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf

# Iniciar Apache en primer plano
echo "Iniciando Apache..."
apache2ctl -D FOREGROUND
