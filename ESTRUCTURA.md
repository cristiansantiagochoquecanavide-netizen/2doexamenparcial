# Estructura del Proyecto - Backend y Frontend Separados

Este proyecto ahora tiene una estructura organizada con carpetas separadas para backend (Laravel) y frontend (React).

## Estructura del Proyecto

```
proyecto/
├── backend/                 # Todo el código de Laravel (PHP)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   ├── composer.json
│   ├── composer.lock
│   └── phpunit.xml
│
├── frontend/                # Todo el código de React y Vite
│   ├── resources/
│   ├── node_modules/
│   ├── public/
│   ├── package.json
│   ├── package-lock.json
│   ├── vite.config.js
│   ├── tailwind.config.js
│   └── postcss.config.js
│
├── Dockerfile              # Construye ambas partes (backend y frontend)
├── render.yaml            # Configuración para Render
├── start-server.sh        # Script de inicio en Render
└── README.md              # Este archivo
```

## Desarrollo Local

### Requisitos
- PHP 8.2+
- Node.js 18+
- Composer
- npm

### Setup Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Setup Frontend

```bash
cd frontend
npm install
npm run dev
```

### Ejecutar Localmente

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```

Accede a `http://localhost:5173` (o el puerto que muestre Vite)

## Despliegue en Render

El `Dockerfile` está configurado para:
1. Compilar el backend PHP con Composer
2. Compilar el frontend con Vite/React
3. Servir todo a través de Apache

La configuración de Render (`render.yaml`) está lista para desplegar automáticamente cuando hagas push a los repositorios.

### Variables de Entorno

Configura estas variables en Render:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY=` (base64 key)
- `APP_URL=` (tu URL en Render)
- `DB_CONNECTION=pgsql`
- `DB_HOST=` (host de la BD)
- `DB_USERNAME=` (usuario de la BD)
- `DB_PASSWORD=` (contraseña de la BD)

## Comandos Útiles

### Backend
```bash
cd backend

# Migraciones
php artisan migrate

# Seeders
php artisan db:seed

# Cache
php artisan cache:clear
php artisan config:cache

# Tests
php artisan test
```

### Frontend
```bash
cd frontend

# Desarrollo
npm run dev

# Producción
npm run build

# Preview
npm run preview
```

## Notas Importantes

- Después de cambios en backend, reconstruye la imagen Docker
- El frontend debe compilarse antes de desplegar (incluido en el Dockerfile)
- Los archivos estáticos se sirven desde `frontend/public`
- El backend espera rutas API en `/api/*`

## Solución de Problemas

### Las migraciones fallan en Render
- Verifica que las variables de BD estén configuradas correctamente
- Revisa los logs en Render

### Assets no cargan en frontend
- Asegúrate de que Vite compiló correctamente
- Revisa que el `public/build/manifest.json` exista

### Laravel no encuentra archivos
- Verifica que las rutas en `routes/web.php` sean correctas
- Revisa el `.htaccess` en `frontend/public`
