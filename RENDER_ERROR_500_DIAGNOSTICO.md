# Guía de Diagnóstico: Error 500 en Render

## 🔴 Causas comunes del error 500

### 1. **Problema de conexión a PostgreSQL**
- La base de datos no está lista cuando inicia la aplicación
- Variables de entorno no configuradas correctamente
- Puerto o host incorrecto

**Solución:**
- Verificar que `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` están configurados
- Usar `psql` para probar conexión directa
- Revisar logs de Render

### 2. **Migraciones fallidas**
- Schema `carga_horaria` no existe
- Tablas no se crearon correctamente
- Constraints o tipos de datos inválidos

**Solución:**
```bash
# Crear schema manualmente
psql -h $DB_HOST -U $DB_USERNAME -d $DB_DATABASE -c "CREATE SCHEMA IF NOT EXISTS carga_horaria"

# Ejecutar migraciones
php artisan migrate --force
```

### 3. **APP_KEY no configurado**
- APP_KEY está vacío o inválido
- Laravel no puede encriptar datos

**Solución:**
```bash
php artisan key:generate --force
```

### 4. **Permisos de archivos**
- Directorios storage/ y bootstrap/cache/ no son escribibles
- Archivos de log no se pueden crear

**Solución en Docker:**
```dockerfile
RUN mkdir -p storage/logs bootstrap/cache && chmod -R 777 storage bootstrap
```

### 5. **Problemas de dependencias**
- Vendor no instalado correctamente
- Paquetes PHP faltantes (pdo_pgsql)

**Solución:**
```bash
composer install --no-dev --optimize-autoloader
```

## 🔧 Pasos de diagnóstico en Render

### 1. Habilitar APP_DEBUG
```yaml
- key: APP_DEBUG
  value: "true"
```

### 2. Revisar los logs
En Render Dashboard → Logs → Ver detalles del error

### 3. Conectarse a la base de datos
```bash
psql postgres://user:password@host:5432/database
```

### 4. Verificar estado de migraciones
```sql
-- En PostgreSQL
SELECT * FROM information_schema.tables 
WHERE table_schema = 'carga_horaria';
```

## 📝 Checklist de configuración

- [ ] `APP_KEY` está configurada (no vacía)
- [ ] `APP_ENV=production` o `production`
- [ ] `APP_DEBUG=true` (temporalmente para debugging)
- [ ] `DB_HOST` apunta a Render PostgreSQL
- [ ] `DB_SSLMODE=require` (para Render)
- [ ] `DB_SCHEMA=carga_horaria` existe en PostgreSQL
- [ ] `FRONTEND_URL` y `VITE_API_URL` configuradas correctamente
- [ ] `start-server.sh` tiene permisos de ejecución (chmod +x)
- [ ] Dockerfile instala todas las extensiones PHP necesarias

## 🚀 Próximos pasos si sigue el error

1. **Verificar logs de Docker en Render:**
   - Render Dashboard → Logs
   - Buscar "error" o "exception"

2. **Recrear la aplicación:**
   - Hacer un re-deploy forzado desde Render
   - O usar: `git push` (trigger automático)

3. **Prueba local:**
   ```bash
   docker build -t test .
   docker run -it test bash
   # Dentro del contenedor:
   php artisan migrate --force
   php artisan serve
   ```

4. **Contactar soporte:**
   - Si persiste, revisar documentación oficial de Render
   - Verificar límites de recursos

## 📞 Variables de Render esperadas

```yaml
DB_HOST=dpg-XXXXXX.postgres.render.com
DB_PORT=5432
DB_DATABASE=appwebcargahoraria
DB_USERNAME=appwebcargahoraria_user
DB_PASSWORD=XXXXXXXXXXXXXXXXXXX
DB_SSLMODE=require
```

Nota: Render proporciona estas variables automáticamente si se vincula la base de datos.
