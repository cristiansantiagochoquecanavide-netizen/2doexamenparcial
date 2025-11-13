# 🔐 Credenciales de Prueba - Sistema de Carga Horaria

## 👤 Usuarios de Prueba

### Administrador
- **Cédula**: 1234567
- **Contraseña**: password123

### Coordinador Académico
- **Cédula**: 2345678
- **Contraseña**: password123

### Docente
- **Cédula**: 3456789
- **Contraseña**: password123

## 📊 Base de Datos Local

### Configuración (.env)
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=appwebcargahoraria
DB_USERNAME=postgres
DB_PASSWORD=CAMPEON
DB_SCHEMA=carga_horaria
DB_SSLMODE=disable
```

### Iniciar servidor local

**PowerShell:**
```powershell
.\start-local.ps1
```

**Bash:**
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## 🌐 URLs

- **Frontend**: http://localhost:3000
- **Backend API**: http://127.0.0.1:8000
- **Login**: http://127.0.0.1:8000/login

## 📝 Notas

- Las credenciales están en la tabla `usuarios`
- El schema es `carga_horaria`
- Las contraseñas están hasheadas con bcrypt
- El token de sesión se almacena en `localStorage` del navegador

## 🐛 Troubleshooting

### Si ves "Las credenciales proporcionadas son incorrectas"

1. Verifica que PostgreSQL está corriendo en localhost:5432
2. Verifica las credenciales en .env
3. Ejecuta: `php artisan migrate --force`
4. Ejecuta: `php artisan db:seed --force`
5. Limpia caché: `php artisan cache:clear`
6. Limpia cookies del navegador (F12 → Storage → Cookies)

### Si la BD no existe

```bash
php artisan tinker
DB::statement('CREATE SCHEMA IF NOT EXISTS carga_horaria')
exit
php artisan migrate --force
php artisan db:seed --force
```
