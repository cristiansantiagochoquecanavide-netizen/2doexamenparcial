echo "=========================================="
echo "Iniciando Apache en puerto $PORT..."
#!/bin/bash

echo "=========================================="
echo "Iniciando Laravel en Render"
echo "=========================================="

# Limpiar caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Migraciones
php artisan migrate --force || true

# Iniciar servidor Laravel en el puerto asignado por Render
echo "Iniciando servidor en puerto $PORT..."
php artisan serve --host 0.0.0.0 --port $PORT

