#!/bin/bash
set -e

echo "🚀 Iniciando aplicación..."

# Ejecutar migraciones
echo "📦 Ejecutando migraciones..."
php artisan migrate:fresh --seed --seeder=UsuarioTestSeeder --force || true

# Cachear configuración
echo "⚙️ Cacheando configuración..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "✅ Inicialización completada"
echo "🌐 Iniciando servidor..."

# Iniciar el servidor PHP
exec vendor/bin/heroku-php-nginx -C nginx.conf public/
