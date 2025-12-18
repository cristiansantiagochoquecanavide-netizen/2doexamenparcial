# 🐳 DESPLEGAR EN RENDER CON DOCKER (5 MINUTOS)

## ✅ ARCHIVOS YA LISTOS

- ✅ `Dockerfile` - Imagen optimizada para producción
- ✅ `.dockerignore` - Excluye archivos innecesarios
- ✅ `nginx.conf` - Configurado para Docker
- ✅ `app:initialize` - Comando para inicializar BD

---

## 🚀 PASO 1: Crear BD PostgreSQL en Render

1. Ve a: https://render.com/dashboard
2. Click **"New +"** → **"PostgreSQL"**
3. **Name:** `appwebcargahoraria-db`
4. **Region:** `Oregon` (cercana)
5. Click **"Create Database"**
6. **⏳ Espera 5-10 minutos**

**Cuando esté listo, copia:**
```
Host: dpg-xxxxx.onrender.com
Port: 5432
Database: postgres
User: postgres
Password: xxxxx
```

---

## 🎯 PASO 2: Crear Web Service con Docker

1. Click **"New +"** → **"Web Service"**
2. Selecciona tu repositorio GitHub
3. Click **"Connect"**

### Configuración:

| Campo | Valor |
|-------|-------|
| **Name** | `appwebcargahoraria` |
| **Region** | `Oregon` |
| **Branch** | `master` |
| **Root Directory** | `backend` |
| **Runtime** | `Docker` |
| **Plan** | Free |

4. Click **"Create Web Service"**

---

## 🔧 PASO 3: Configurar Variables de Entorno

Mientras se construye la imagen, agrega las variables:

1. En el servicio → **"Environment"**
2. Agrega cada una:

```
APP_NAME=AppWebCargaHoraria
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:VPuXqWlyLax+DN2E/gda6wTVtlES3EkJJquGkv3HE1U=
APP_URL=https://appwebcargahoraria.onrender.com
ASSET_URL=https://appwebcargahoraria.onrender.com
FRONTEND_URL=https://2doexamenparcial.vercel.app

DB_CONNECTION=pgsql
DB_HOST=dpg-xxxxx.onrender.com
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

3. Click **"Save"**

---

## 📋 PASO 4: Monitorear Build y Deploy

1. En el servicio → **"Logs"**
2. Espera a ver:

```
Building Docker image...
Step 1/10: FROM php:8.2-fpm-alpine
...
✅ Successfully built
...
🚀 Iniciando aplicación en Docker...
📦 Ejecutando migraciones...
👥 Ejecutando seeder...
⚙️ Cacheando configuración...
✅ Inicialización completada
🌐 Iniciando servidor...
```

**Tiempo total:** 5-7 minutos (primera vez tarda más)

---

## ✅ PASO 5: Verificar Backend

Una vez que veas "Iniciando servidor", prueba:

```
https://appwebcargahoraria.onrender.com/api/health
```

Deberías ver:
```json
{
  "status": "ok",
  "database": "connected",
  "usuarios_count": 1
}
```

Si ves eso → ✅ **Backend listo**

---

## 📱 PASO 6: Actualizar Frontend (Vercel)

1. Abre: `frontend/.env.production`
2. Reemplaza:
   ```
   VITE_API_URL=https://appwebcargahoraria.onrender.com/api
   ```

3. Pushea:
   ```bash
   git add frontend/.env.production
   git commit -m "Actualizar API URL para Render Docker"
   git push
   ```

4. Vercel se reconstruye automáticamente (2 min)

---

## 🧪 PASO 7: Probar Login

1. Abre: https://2doexamenparcial.vercel.app
2. Intenta:
   - **CI:** `12345678`
   - **Contraseña:** `12345678`

Si funciona → ✅ **¡LISTO!**

---

## 📊 FINAL

| Componente | URL |
|-----------|-----|
| **Backend** | https://appwebcargahoraria.onrender.com |
| **Frontend** | https://2doexamenparcial.vercel.app |
| **BD** | PostgreSQL en Render (privada) |
| **API** | https://appwebcargahoraria.onrender.com/api |

---

## 🆘 TROUBLESHOOTING

### Build falla:
- Mira el error exacto en Logs
- Probablemente falta extension PHP
- Verifica `Dockerfile` sintaxis

### Deploy lento:
- Docker tarda la primera vez (5-10 min)
- Las siguientes son más rápidas (1-2 min)

### Error 404:
- ¿Viste "Iniciando servidor"?
- Si no, build falla → revisa logs

### Error de BD:
- Verifica credenciales exactas
- ¿BD está "Available"?
- Test con DBeaver

### Usuario no existe:
- En Render Shell, ejecuta:
  ```
  php artisan app:initialize
  ```

---

## 💡 VENTAJAS DE DOCKER

✅ Consistencia local/producción
✅ Fácil de escalar
✅ Mejor control del entorno
✅ Más profesional
✅ Compatible con otros servicios

---

**¡A desplegar! 🚀**
