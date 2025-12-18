# ✅ CHECKLIST: Variables de Entorno para Render

Tu `.env` local ya está actualizado para Render. Ahora necesitas copiar **EXACTAMENTE** estas variables en Render Dashboard:

---

## 🎯 PASO: Agregar Variables en Render Dashboard

1. Ve a: https://render.com/dashboard
2. Selecciona tu Web Service `appwebcargahoraria`
3. Click en: **Environment** (lado izquierdo)
4. Copia **EXACTAMENTE** cada variable de abajo

### Variables a Copiar:

```
APP_NAME=AppWebCargaHoraria
APP_ENV=production
APP_KEY=base64:VPuXqWlyLax+DN2E/gda6wTVtlES3EkJJquGkv3HE1U=
APP_DEBUG=false
APP_URL=https://appwebcargahoraria.onrender.com
ASSET_URL=https://appwebcargahoraria.onrender.com
FRONTEND_URL=https://2doexamenparcial.vercel.app

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=dpg-xxxxx.onrender.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=xxxxxxxxxxxxx
DB_SCHEMA=public
DB_SSLMODE=require
DB_TIMEOUT=60
DB_CONNECT_TIMEOUT=60

CACHE_STORE=file
CACHE_PREFIX=appwebcargahoraria_

SESSION_DRIVER=file
SESSION_LIFETIME=120

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

MAIL_MAILER=log
```

---

## ⚠️ IMPORTANTE: Reemplazar Valores

**Estos valores tienes que actualizarlos con los REALES:**

### `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`

Obten de tu BD PostgreSQL en Render:
1. Dashboard → PostgreSQL Service
2. Copia:
   - **Host:** `dpg-xxxxx.onrender.com`
   - **Port:** `5432`
   - **Database:** `postgres` (o el que creaste)
   - **User:** `postgres`
   - **Password:** `xxxxx` (la que generó Render)

Luego en Environment variables:
```
DB_HOST=dpg-xxxxx.onrender.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=xxxxxxxxxxxxx
DB_SCHEMA=public
DB_SSLMODE=require
```

### `APP_URL` y `ASSET_URL`

Render te asignará una URL como:
```
https://appwebcargahoraria.onrender.com
```

Copia esa URL en:
```
APP_URL=https://appwebcargahoraria.onrender.com
ASSET_URL=https://appwebcargahoraria.onrender.com
```

---

## ✅ VERIFICACIÓN FINAL

Antes de hacer deploy, verifica:

- [ ] `APP_ENV=production` (NO `local`)
- [ ] `APP_DEBUG=false` (NO `true`)
- [ ] `DB_HOST` tiene valor (NO vacío)
- [ ] `DB_PASSWORD` correcto (credenciales de Render)
- [ ] `APP_URL` es HTTPS (NO HTTP)
- [ ] `FRONTEND_URL` es Vercel (NO localhost)
- [ ] `SSLMODE=require` (seguridad)
- [ ] `CACHE_PREFIX` está definido

---

## 🚀 DESPUÉS DE AGREGAR VARIABLES

1. Click **"Save"** en Environment
2. Render **redeploy automáticamente**
3. Espera a ver "Server running" en Logs
4. Verifica: `https://appwebcargahoraria.onrender.com/api/health`

---

## ❌ ERRORES COMUNES

**Error: "SQLSTATE[HY000]: Database connection failed"**
- ❌ DB_HOST incorrecto
- ❌ DB_PASSWORD incorrecto
- ❌ BD no está "Available"

**Error: "App crashed"**
- ❌ APP_KEY vacío
- ❌ DB_HOST vacío
- ❌ APP_ENV no es `production`

**Error: 404 en /api/health**
- ❌ App no inicializó
- ❌ Puerto incorrecto
- ❌ Docker no se compiló bien

---

## 📋 RESUMEN RÁPIDO

1. ✅ `.env` local está actualizado
2. ✅ CORS está configurado
3. ✅ Código en GitHub
4. ⏳ **Falta:** Crear BD PostgreSQL en Render
5. ⏳ **Falta:** Crear Web Service en Render
6. ⏳ **Falta:** Agregar variables en Render Environment
7. ⏳ **Falta:** Deploy

---

**¿Necesitas ayuda creando la BD o Web Service en Render? Avísame.** 👍
