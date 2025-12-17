# 🚀 CÓMO FORZAR REDEPLOY EN RAILWAY - URGENTE

El cambio ya está en GitHub pero Railway necesita ser forzado a redeploy.

## OPCIÓN 1: Cambiar Variable de Entorno (RÁPIDO - 30 segundos)

1. Ve a: https://railway.app → Tu Proyecto → Variables
2. Busca una variable cualquiera (ej: `APP_ENV`)
3. Cambia su valor y guarda
4. Railway detectará el cambio y hará REDEPLOY automático
5. Espera 5-10 minutos

## OPCIÓN 2: Hacer Commit Vacío en Git (2 minutos)

```bash
cd backend
git commit --allow-empty -m "Force Railway redeploy with new initialization command"
git push
```

## OPCIÓN 3: Desconectar y Reconectar GitHub (más lento pero seguro)

1. Railway Dashboard → Settings
2. Desconectar GitHub
3. Reconectar
4. Redeploy iniciará automáticamente

---

## 🔍 MONITOREAR EL REDEPLOY

Una vez iniciado el redeploy:

1. Ve a Railway Dashboard → Logs
2. Deberías ver output como:

```
🚀 Iniciando aplicación...
📦 Ejecutando migraciones...
✅ Migraciones completadas
👥 Creando usuario de prueba...
   ✅ Persona creada (ID: 1)
   ✅ Rol encontrado (ID: 1)
   ✅ Usuario creado (ID: 1)
   📝 Credenciales: CI=12345678 | Contraseña=12345678
✅ Usuario de prueba creado/verificado
⚙️ Cacheando configuración...
✅ Cache actualizado
✅ Inicialización completada exitosamente
```

3. Luego verás: `INFO Server running on [http://0.0.0.0:8080]`

---

## 📝 DESPUÉS DEL REDEPLOY

1. Intenta login nuevamente en:
   - https://2doexamenparcial.vercel.app
   - CI: `12345678`
   - Contraseña: `12345678`

2. Si aún falla, abre tu navegador en:
   - https://2doexamenparcial.vercel.app/test-api-railway.html
   - Prueba los tests para debuggear el problema

---

## ⚠️ SI SIGUE SIN FUNCIONAR

Información que necesitarás compartir:
1. Screenshot del Deploy Logs (últimas 50 líneas)
2. Screenshot del error exacto en el navegador
3. Confirmación de que redeploy ocurrió

Posibles problemas:
- ❌ Release phase no ejecutó → Ver logs de errores
- ❌ BD offline → Ver conectividad en Railway
- ❌ CORS issue → Recalcular origen en frontend
- ❌ Rutas no encontradas → Error en nginx.conf
