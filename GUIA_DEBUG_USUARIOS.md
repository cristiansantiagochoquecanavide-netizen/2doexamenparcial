# 🔧 Guía para Debuggear la Creación de Usuarios en Railway

## Estado Actual
- ✅ Backend desplegado en Railway
- ✅ Base de datos PostgreSQL conectada
- ✅ Migraciones disponibles
- ❌ **Usuario de prueba NO se está creando automáticamente**

## Problema Identificado
El script `start.sh` debería:
1. Ejecutar migraciones
2. Crear usuario de prueba
3. Iniciar servidor

Pero los Deploy Logs muestran "Nothing to migrate" sin evidencia de que `start.sh` se ejecute.

---

## 🚀 Solución Paso a Paso

### 1. Forzar Redeploy en Railway

Railway detecta cambios en git automáticamente, pero podemos forzar un redeploy:

**Opción A: Hacer un commit vacío (rápido)**
```bash
cd backend
git commit --allow-empty -m "Force Railway redeploy"
git push
```

**Opción B: Cambiar variable de entorno en Railway Dashboard**
1. Ir a https://railway.app
2. Seleccionar el proyecto
3. Ir a Variables
4. Cambiar `RAILWAY_DEPLOYMENT_ID` o agregar `FORCE_REDEPLOY=true`
5. Guardar (esto triggeará un nuevo deploy)

### 2. Monitorear los Deploy Logs

Una vez que inicie el redeploy:
1. En Railway Dashboard → Logs
2. Esperar a ver mensajes como:
   - ✅ `🚀 Iniciando aplicación en modo producción...`
   - ✅ `📦 Ejecutando migraciones...`
   - ✅ `👥 Ejecutando seeder de datos...`
   - ✅ `✅ Inicialización completada`

**Si NO ves estos mensajes:**
- El script `start.sh` no se está ejecutando
- Problema en el `Procfile`

### 3. Verificar el Procfile

El archivo `backend/Procfile` debe tener exactamente esto:
```
web: bash start.sh
```

Si ves algo diferente, actualizar a esto y pushear.

### 4. Si `start.sh` No Se Ejecuta

**Solución A: Usar `release` phase**

Cambiar `backend/Procfile` a:
```
release: php artisan migrate --force && php artisan db:seed --class=UsuarioTestSeeder --force
web: vendor/bin/heroku-php-nginx -C nginx.conf public/
```

Luego:
```bash
git add backend/Procfile
git commit -m "Usar release phase para ejecutar migraciones"
git push
```

**Solución B: Inline directo en Procfile**

```
web: php artisan migrate --force 2>/dev/null; php artisan db:seed --class=UsuarioTestSeeder --force 2>/dev/null; vendor/bin/heroku-php-nginx -C nginx.conf public/
```

### 5. Después del Redeploy: Verificar Usuario Creado

**Método 1: Intentar login**
- IR a: https://2doexamenparcial.vercel.app
- Usar credenciales:
  - CI: `12345678`
  - Contraseña: `12345678`

**Método 2: Verificar directamente (si tienes acceso SSH a Railway)**
```bash
# Conectar a Railway
railway shell

# Entrar a tinker
php artisan tinker

# Listar usuarios
App\Models\Usuario::all();

# Buscar usuario específico
App\Models\Usuario::where('ci_persona', '12345678')->first();
```

---

## 📋 Checklist de Validación

- [ ] Redeploy iniciado en Railway
- [ ] Deploy Logs muestran scripts ejecutándose
- [ ] Migraciones completadas (sin errores)
- [ ] Seeder ejecutado (sin errores)
- [ ] Usuario aparece en base de datos
- [ ] Login en Vercel funciona con credenciales `12345678/12345678`
- [ ] Token devuelto por el servidor
- [ ] Dashboard carga después de login

---

## ⚠️ Posibles Errores y Soluciones

### Error: "SQLSTATE[HY000]: General error"
**Causa:** Problema con permisos de base de datos
**Solución:** Verificar que las variables de entorno en Railway coincidan con las credenciales reales

### Error: "SQLSTATE[42P01] relation \"usuario\" does not exist"
**Causa:** Las migraciones no ejecutaron
**Solución:** Usar `release` phase en Procfile

### Error: "Integrity constraint violation"
**Causa:** Foreign key no coincide entre tablas
**Solución:** Verificar que Persona existe antes de crear Usuario

### Error: "Class not found"
**Causa:** Autoloader no funciona en Procfile
**Solución:** Usar rutas absolutas: `php artisan` → `./artisan` o `php -r "require 'artisan'; ..."` 

---

## 🔍 Debug Adicional

Si nada funciona, prueba esto localmente primero:

```bash
# En tu máquina local
cd backend

# Simular lo que hace start.sh
php artisan migrate --force
php artisan db:seed --class=UsuarioTestSeeder --force
php artisan tinker

# En tinker, verificar:
App\Models\Usuario::count()
App\Models\Usuario::where('ci_persona', '12345678')->first()
```

---

## 📞 Próximos Pasos

1. ✅ Ejecutar el redeploy
2. ✅ Verificar Deploy Logs
3. ✅ Intentar login
4. Si no funciona → Aplicar **Solución A** o **B** del paso 4

**Hora estimada:** 5-10 minutos para redeploy + 2-3 minutos para verificar
