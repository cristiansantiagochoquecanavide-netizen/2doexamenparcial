# Script para iniciar el servidor Laravel en localhost

Write-Host "=========================================="
Write-Host "Iniciando Sistema de Carga Horaria"
Write-Host "=========================================="

# Limpiar caché
Write-Host "Limpiando caché..."
php artisan cache:clear | Out-Null
php artisan config:clear | Out-Null

# Crear schema
Write-Host "Verificando esquema de BD..."
php artisan tinker --execute="DB::statement('CREATE SCHEMA IF NOT EXISTS carga_horaria')" | Out-Null

# Ejecutar migraciones
Write-Host "Ejecutando migraciones..."
php artisan migrate --force 2>&1 | Out-Null

# Iniciar servidor
Write-Host ""
Write-Host "=========================================="
Write-Host "✅ Servidor iniciando..."
Write-Host "=========================================="
Write-Host "URL: http://127.0.0.1:8000"
Write-Host "Presiona Ctrl+C para detener"
Write-Host "=========================================="
Write-Host ""

php artisan serve --host=127.0.0.1 --port=8000
