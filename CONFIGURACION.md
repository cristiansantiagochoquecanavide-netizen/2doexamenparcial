# Configuración del Proyecto - Carga Horaria

## Estado Actual

✅ **Mantenidos:**
- Controllers en `backend/app/Http/Controllers/`
- Pages/Componentes en `frontend/resources/js/pages/`
- Components en `frontend/resources/js/components/`
- Utilities y contexts en `frontend/resources/js/`

❌ **Eliminados:**
- Dockerfile (raíz)
- docker-compose.yml

## Configuración de Entorno

### Backend (.env)
```
APP_NAME=AppWebCargaHoraria
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:5173

# Base de Datos PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=carga_horaria
DB_USERNAME=postgres
DB_PASSWORD=CAMPEON
DB_SCHEMA=carga_horaria
DB_SSLMODE=disable

# Sanctum para API
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,localhost:5173
```

### Frontend (.env)
```
VITE_API_URL=http://127.0.0.1:8000/api
VITE_APP_NAME=Carga Horaria
VITE_APP_ENV=development
```

## Cómo Ejecutar

### 1. Backend (Laravel)
```bash
cd backend
php artisan serve
# Se ejecutará en http://127.0.0.1:8000
```

### 2. Frontend (React + Vite)
```bash
cd frontend
npm install
npm run dev
# Se ejecutará en http://localhost:5173
```

## Estructura de Carpetas Preservada

```
backend/
  app/
    Http/
      Controllers/  ✅ Mantiene todos los controladores
    Models/
    Services/
  config/
  database/
  routes/

frontend/
  resources/
    js/
      pages/        ✅ Mantiene todas las páginas
      components/   ✅ Mantiene todos los componentes
      context/      ✅ Mantiene contextos
      utils/        ✅ Mantiene utilidades
```

## Base de Datos

- **Motor:** PostgreSQL
- **Base:** carga_horaria
- **Usuario:** postgres
- **Contraseña:** CAMPEON
- **Host:** localhost:5432

## API Endpoints

- **Base URL:** http://127.0.0.1:8000/api
- **Frontend accede a:** VITE_API_URL configurado

