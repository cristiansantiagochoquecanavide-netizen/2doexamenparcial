# ⚡ PASO A PASO: DESPLEGAR EN RENDER (5 MINUTOS)

## ✅ YA ESTÁ LISTO EN EL REPO

- ✅ `Procfile` - Configurado para Render
- ✅ `build.sh` - Script de construcción
- ✅ `.env.render.example` - Variables de ejemplo
- ✅ `app:initialize` - Comando para BD

---

## 🚀 PASO 1: Crear BD PostgreSQL en Render

1. Ve a: https://render.com/dashboard
2. Click "New +" → "PostgreSQL"
3. **Name:** `appwebcargahoraria-db`
4. **Region:** `Ohio` (o cercana)
5. Click "Create Database"
6. **Espera 5-10 minutos** a que se cree

Cuando esté listo, copia:
- **Host:** `dpg-...`
- **Port:** `5432`
- **Database:** `postgres`
- **User:** `postgres`
- **Password:** `...`

---

## 🎯 PASO 2: Crear Web Service en Render

1. Click "New +" → "Web Service"
2. **Repository:** Selecciona tu GitHub repo
3. Click "Connect"

**Configuración:**

| Campo | Valor |
|-------|-------|
| **Name** | `appwebcargahoraria-backend` |
| **Root Directory** | `backend` |
| **Build Command** | `./build.sh` |
| **Start Command** | `vendor/bin/heroku-php-nginx -C nginx.conf public/` |
| **Plan** | Free (o Premium) |
| **Region** | Ohio |

4. Click "Create Web Service"
5. **Espera a que inicie el deploy** (~3-5 min)

---

## 🔧 PASO 3: Configurar Variables de Entorno

Mientras se despliega, configura variables:

1. En el servicio → Click "Environment"
2. Agregar cada variable (copia del `.env.render.example`):

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:VPuXqWlyLax+DN2E/gda6wTVtlES3EkJJquGkv3HE1U=
APP_URL=https://appwebcargahoraria-backend.onrender.com
ASSET_URL=https://appwebcargahoraria-backend.onrender.com
FRONTEND_URL=https://2doexamenparcial.vercel.app

DB_CONNECTION=pgsql
DB_HOST=dpg-xxxxxx.onrender.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=xxxxx
DB_SCHEMA=public
DB_SSLMODE=require
DB_TIMEOUT=60

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stack
LOG_LEVEL=info
```

3. Click "Save"

---

## ✅ PASO 4: Verificar Deploy

1. En el servicio → "Logs"
2. Espera a ver:
   ```
   ✅ Build completado exitosamente!
   Server running on [http://0.0.0.0:8080]
   ```

3. Ve a: `https://appwebcargahoraria-backend.onrender.com/api/health`
   - Deberías ver: `"status": "ok"`

Si ves eso → ✅ **Backend listo**

---

## 📱 PASO 5: Actualizar Frontend (Vercel)

1. Abre: `frontend/.env.production`
2. Cambia:
   ```
   VITE_API_URL=https://appwebcargahoraria-backend.onrender.com/api
   ```

3. Guarda y pushea:
   ```bash
   git add frontend/.env.production
   git commit -m "Actualizar API URL para Render"
   git push
   ```

4. Vercel se reconstruye automáticamente (2-3 min)

---

## 🧪 PASO 6: Probar Login

1. Abre: https://2doexamenparcial.vercel.app
2. Intenta login:
   - **CI:** `12345678`
   - **Contraseña:** `12345678`

Si funciona → ✅ **¡TODO LISTO!**

---

## 🆘 SI FALLA

### Error en Deploy Logs:
- Copia el error exacto
- Probablemente falta variable (DB_HOST, etc)
- Verifica que DB está online

### Error 404 en /api/health:
- El servidor no inicializó correctamente
- Mira Build Command en Logs
- ¿Ejecutó `build.sh`?

### Error de BD:
- Verifica credenciales de PostgreSQL
- ¿Está "Available" la BD en Render Dashboard?
- Test conexión con DBeaver o similar

### Error en login:
- Verifica que `/api/health` funciona
- ¿Tiene usuarios_count: 1?
- Si no, ejecuta manualmente en Render Shell:
  ```
  php artisan app:initialize
  ```

---

## 📊 URLs FINALES

- **Backend:** https://appwebcargahoraria-backend.onrender.com
- **Frontend:** https://2doexamenparcial.vercel.app
- **DB:** PostgreSQL en Render (privada)

**¡Listo para producción!** 🎉
