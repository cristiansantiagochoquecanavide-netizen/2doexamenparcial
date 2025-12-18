#!/bin/bash
# Build script para Render

set -e

echo "🏗️ Build iniciado para Render..."

# Instalar dependencias
echo "📦 Instalando dependencias con Composer..."
composer install --prefer-dist --no-dev --optimize-autoloader

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generando APP_KEY..."
    php artisan key:generate
fi

# Limpiar caches previos
echo "🧹 Limpiando caches..."
php artisan optimize:clear || true

# Ejecutar migraciones y seeder
echo "💾 Ejecutando migraciones..."
php artisan migrate --force --no-interaction

echo "👥 Ejecutando seeder..."
php artisan db:seed --class=UsuarioTestSeeder --force --no-interaction || true

# Cachear configuración
echo "⚙️  Cacheando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Build completado exitosamente!"
