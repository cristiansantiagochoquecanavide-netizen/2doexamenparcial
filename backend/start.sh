#!/bin/bash
set -e

echo "🚀 Iniciando aplicación en modo producción..."
echo "📋 Información del entorno:"
echo "   APP_ENV: $APP_ENV"
echo "   APP_DEBUG: $APP_DEBUG"
echo "   DB_HOST: $DB_HOST"
echo "   DB_PORT: $DB_PORT"

# Ejecutar migraciones
echo ""
echo "📦 Ejecutando migraciones..."
if php artisan migrate --force; then
    echo "✅ Migraciones completadas"
else
    echo "⚠️ Error en migraciones o nada que migrar"
fi

# Ejecutar seeder
echo ""
echo "👥 Ejecutando seeder de datos..."
if php artisan db:seed --class=UsuarioTestSeeder --force; then
    echo "✅ Seeder completado"
else
    echo "⚠️ Error en seeder"
fi

# Cachear configuración
echo ""
echo "⚙️ Cacheando configuración..."
php artisan config:cache || echo "⚠️ Error cacheando config"
php artisan route:cache || echo "⚠️ Error cacheando rutas"
php artisan view:cache || echo "⚠️ Error cacheando vistas"

echo ""
echo "✅ Inicialización completada"
echo "🌐 Iniciando servidor en puerto 8080..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Iniciar el servidor PHP
exec vendor/bin/heroku-php-nginx -C nginx.conf public/
