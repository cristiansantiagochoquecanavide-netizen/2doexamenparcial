# 🚀 GUÍA COMPLETA: DESPLEGAR BACKEND EN RENDER

## PASO 1: Crear Cuenta en Render

1. Ve a: https://render.com
2. Crea cuenta (puedes usar GitHub)
3. Conecta tu GitHub a Render

---

## PASO 2: Preparar el Backend para Render

### Archivos Necesarios (YA ESTÁN LISTOS):

✅ `Procfile` - Comando para iniciar el servidor
✅ `build.sh` - Script de build
✅ `.env.render` - Variables de entorno para Render

### Variables de Entorno en Render:

Necesitarás configurar estas variables en el Dashboard de Render:

```
APP_NAME=AppWebCargaHoraria
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:VPuXqWlyLax+DN2E/gda6wTVtlES3EkJJquGkv3HE1U=
APP_URL=https://TU_URL_RENDER.onrender.com
ASSET_URL=https://TU_URL_RENDER.onrender.com

FRONTEND_URL=https://TU_URL_VERCEL.vercel.app

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=TU_PASSWORD_POSTGRES
DB_SCHEMA=public
DB_SSLMODE=require
DB_TIMEOUT=60

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## PASO 3: Crear PostgreSQL Database en Render

### Opción A: BD Gratuita en Render (Limitada)

1. En Render Dashboard → New +
2. Selecciona "PostgreSQL"
3. Dale un nombre (ej: `appwebcargahoraria-db`)
4. Region: USA (o la más cercana)
5. Click "Create Database"
6. Espera a que se cree (5-10 min)
7. Copia los credenciales

**Conexión en Render:**
- Host: `dpg-...` (te lo da Render)
- Port: `5432`
- Database: `postgres` (viene por defecto)
- Username: `postgres`
- Password: Te lo da Render

### Opción B: BD Externa (Recomendado si es gratis)

Puedes usar:
- Aiven.io (PostgreSQL gratuito)
- Railway (que ya tienes)
- ElephantSQL (gratuito con limitaciones)

**Recomendación:** Usa la BD de Render que ya tienes en Railway. Simplemente reutiliza los mismos credenciales.

---

## PASO 4: Crear Servicio en Render

1. En Dashboard de Render → New +
2. Selecciona "Web Service"
3. Conecta tu repositorio GitHub
4. Configuración:

   **Name:** `appwebcargahoraria-backend` (o similar)
   
   **Root Directory:** `backend`
   
   **Build Command:**
   ```
   ./build.sh
   ```
   
   **Start Command:**
   ```
   vendor/bin/heroku-php-nginx -C nginx.conf public/
   ```
   
   **Environment:**
   - Plan: Free (o el que prefieras)
   - Region: Ohio (USA) o cercana

5. Click "Create Web Service"

---

## PASO 5: Agregar Variables de Entorno en Render

Dentro del servicio creado:

1. Ir a Settings → Environment
2. Agregar variables (puedes copiar del `.env`):

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:VPuXqWlyLax+DN2E/gda6wTVtlES3EkJJquGkv3HE1U=
APP_URL=https://appwebcargahoraria-backend.onrender.com
ASSET_URL=https://appwebcargahoraria-backend.onrender.com
FRONTEND_URL=https://2doexamenparcial.vercel.app

DB_CONNECTION=pgsql
DB_HOST=dpg-xxxxx.onrender.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=xxxxxxxxxxxx
DB_SCHEMA=public
DB_SSLMODE=require
DB_TIMEOUT=60
```

3. Click "Save"

---

## PASO 6: Configurar CORS para Render

Una vez que tengas la URL de Render, actualiza `backend/config/cors.php`:

```php
'allowed_origins' => [
    'https://2doexamenparcial.vercel.app',
    'https://appwebcargahoraria-backend.onrender.com', // ← Agregar tu URL de Render
    'http://localhost:5173',
],
```

Luego pushea a GitHub:
```bash
git add backend/config/cors.php
git commit -m "Actualizar CORS para Render backend"
git push
```

---

## PASO 7: Monitorear Deploy

1. En Render Dashboard del servicio → Logs
2. Espera a que complete:
   - ✅ Build iniciado
   - ✅ Dependencias instaladas
   - ✅ Migraciones ejecutadas
   - ✅ Seeder ejecutado
   - ✅ Server iniciado

3. Verás mensaje final:
   ```
   Server running on [http://0.0.0.0:8080]
   ```

---

## PASO 8: Verificar Backend en Render

Una vez deployed:

1. Abre: `https://appwebcargahoraria-backend.onrender.com/api/health`
   
   Deberías ver:
   ```json
   {
     "status": "ok",
     "timestamp": "2025-12-17...",
     "database": "connected",
     "usuarios_count": 1
   }
   ```

2. Si ves eso → ✅ Backend funcionando

---

## PASO 9: Actualizar Frontend para Render

En `frontend/.env.production`, cambia:

```
VITE_API_URL=https://appwebcargahoraria-backend.onrender.com/api
```

Luego:
```bash
git add frontend/.env.production
git commit -m "Actualizar API URL para Render backend"
git push
```

**El frontend de Vercel se reconstruirá automáticamente.**

---

## PASO 10: Probar Login

1. Ve a: `https://2doexamenparcial.vercel.app`
2. Intenta login:
   - CI: `12345678`
   - Contraseña: `12345678`

Si funciona → ✅ ¡TODO LISTO!

---

## 📊 COMPARACIÓN: Railway vs Render

| Aspecto | Railway | Render |
|---------|---------|--------|
| **Precio** | Freemium ($5/mes mínimo) | Free (con limitaciones) |
| **BD PostgreSQL** | Incluida ($0.28/día) | Gratuita (limitada) |
| **Uptime** | 99.9% | 99.5% |
| **Performance** | Muy bueno | Bueno |
| **Facilidad** | Media | Fácil |
| **Soporte** | Bueno | Excelente |

---

## ⚠️ NOTAS IMPORTANTES

1. **URL de Render será similar a:**
   ```
   https://appwebcargahoraria-backend.onrender.com
   ```

2. **Si es plan FREE:**
   - Se detiene después de 15 minutos sin tráfico
   - Tarda ~30 segundos en "despertarse"
   - Para producción real: considera plan de pago

3. **BD Gratuita en Render:**
   - Máximo 256MB
   - Se elimina después de 90 días sin uso
   - Para producción: usa BD externa

4. **Si necesitas BD más potente:**
   - Sigue usando Railway para BD
   - Y Render solo para el servidor web

---

## 🆘 TROUBLESHOOTING

### Error: "Build failed"
- Verifica que `root directory` apunta a `backend/`
- Chequea que `build.sh` existe y es ejecutable

### Error: "App crashed"
- Ve a Logs y busca el error
- Probablemente falta variable de entorno
- Verifica DB_HOST, DB_PASSWORD, etc.

### Error: "Database connection failed"
- Asegúrate que credenciales son correctas
- Verifica que BD está en línea
- Chequea DB_SSLMODE=require

### Error: "404 on auth/login"
- Probablemente BD no tiene usuario
- Ejecuta manualmente en Render Shell:
  ```
  php artisan app:initialize
  ```

---

## 🎯 RESUMEN RÁPIDO

1. ✅ Crear cuenta en Render
2. ✅ Crear BD PostgreSQL (o usar existente)
3. ✅ Crear Web Service con GitHub
4. ✅ Root Directory: `backend`
5. ✅ Agregar variables de entorno
6. ✅ Deploy automático
7. ✅ Verificar `/api/health`
8. ✅ Actualizar CORS y URLs
9. ✅ Probar login desde Vercel

**Tiempo total:** 15-20 minutos
