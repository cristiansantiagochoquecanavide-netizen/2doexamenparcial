# Configuración de Variables de Entorno en Render

## Variables Requeridas en Render Dashboard

Debes configurar las siguientes variables de entorno en tu servicio de Render (Web Service):

### Aplicación
```
APP_NAME=AppWebCargaHoraria
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:VPuXqWlyLax+DN2E/gda6wTVtlES3EkJJquGkv3HE1U=
APP_URL=https://twodoexamenparcial.onrender.com
ASSET_URL=https://twodoexamenparcial.onrender.com
FRONTEND_URL=https://2doexamenparcial.vercel.app
```

### Base de Datos PostgreSQL
```
DB_CONNECTION=pgsql
DB_HOST=dpg-d51m9e56ubrc738q1k10-a.com
DB_PORT=5432
DB_DATABASE=appwebcargahoraria_e5dz
DB_USERNAME=appwebcargahoraria_e5dz_user
DB_PASSWORD=8kcHnQuBPAV98N9HkJOJLKceXqU3DnTA
DB_SCHEMA=public
DB_SSLMODE=require
DB_TIMEOUT=60
DB_CONNECT_TIMEOUT=60
```

### Logging y Caché
```
LOG_CHANNEL=stack
LOG_LEVEL=info
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
BCRYPT_ROUNDS=12
```

## Pasos para Configurar en Render

1. Ve a tu servicio Web Service en Render Dashboard
2. Haz clic en **Environment**
3. Agrega cada variable arriba (o importa desde un archivo `.env`)
4. Haz clic en **Save Changes**
5. Redeploya la aplicación

## Obtener Credenciales de PostgreSQL

Si la base de datos está en Render:
1. Crea un servicio PostgreSQL en Render
2. Copia las credenciales de conexión
3. Reemplaza `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` y `DB_DATABASE` con tus valores reales

Si la base de datos está en otro servidor (p.ej., Railway):
1. Verifica que el hostname sea alcanzable desde Render
2. Asegúrate de que el firewall permita conexiones desde Render
3. Prueba la conectividad configurando `DB_SSLMODE=disable` temporalmente (NO para producción)

## Verificar Conectividad

Una vez desplegado, verifica los logs de Render:
- Las migraciones deberían ejecutarse automáticamente
- El seeder debería crear un usuario de prueba
- Deberías ver "✅ Inicialización completada"
