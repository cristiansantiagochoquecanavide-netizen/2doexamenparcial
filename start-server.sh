#!/bin/bash

# Esperar a que PostgreSQL esté listo (aumentado a 30 segundos)
sleep 30

# Intentar conectar a la base de datos con reintentos
for i in {1..10}; do
  echo "Intento de conexión $i..."
  if pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME > /dev/null 2>&1; then
    echo "PostgreSQL está listo"
    break
  fi
  sleep 5
done

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force || true

# Crear el schema
echo "Creando schema..."
php artisan tinker --execute="DB::statement('CREATE SCHEMA IF NOT EXISTS carga_horaria')" || true

# Iniciar servidor
echo "Iniciando servidor..."
php artisan serve --host=0.0.0.0 --port=10000
