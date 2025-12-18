# 🐳 Docker Setup para Desarrollo Local

## Requisitos

- Docker y Docker Compose instalados
- Git
- ~2GB de espacio en disco

## 🚀 OPCIÓN 1: Desarrollo Local con Docker

### Paso 1: Construir e iniciar contenedores

```bash
docker-compose up --build
```

Espera a ver mensajes como:
```
✅ Inicialización completada
📱 Frontend running at http://localhost:5173
```

### Paso 2: Verificar que todo funciona

**Backend:**
```bash
curl http://localhost:8000/api/health
```

Deberías ver:
```json
{"status": "ok", "database": "connected"}
```

**Frontend:**
- Abre: http://localhost:5173

### Paso 3: Probar Login

1. Frontend: http://localhost:5173
2. CI: `12345678`
3. Contraseña: `12345678`

---

## 📊 Servicios Activos

| Servicio | URL | Puerto |
|----------|-----|--------|
| **Backend** | http://localhost:8000 | 8000 |
| **Frontend** | http://localhost:5173 | 5173 |
| **PostgreSQL** | localhost:5432 | 5432 |

---

## 🛠️ Comandos Útiles

### Detener todo
```bash
docker-compose down
```

### Detener y eliminar volúmenes (BD)
```bash
docker-compose down -v
```

### Ver logs
```bash
# Backend
docker-compose logs backend

# Frontend
docker-compose logs frontend

# BD
docker-compose logs postgres
```

### Ejecutar comandos en backend
```bash
docker-compose exec backend php artisan tinker
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
```

### Acceder a la BD
```bash
docker-compose exec postgres psql -U postgres -d appwebcargahoraria
```

### Reconstruir después de cambios
```bash
docker-compose down
docker-compose up --build
```

---

## 📝 OPCIÓN 2: Compilar solo para Render

Si solo quieres compilar la imagen para Render (sin correr):

```bash
cd backend
docker build -t appwebcargahoraria:latest .
```

Esto crea la imagen `appwebcargahoraria:latest` que Render usará automáticamente.

---

## 🐛 Troubleshooting

### "Port already in use"
```bash
# Cambiar puerto en docker-compose.yml
# O: Detener el servicio anterior
docker ps
docker stop <container_id>
```

### "Cannot connect to database"
```bash
# Asegúrate que postgres está corriendo
docker-compose ps

# Reinicia los servicios
docker-compose restart
```

### "npm ERR! code ERESOLVE"
```bash
# Usa legacy peer deps
npm install --legacy-peer-deps
```

---

## 📦 Volúmenes y Persistencia

- `postgres_data`: Almacena la BD de PostgreSQL
- `app_storage`: Almacena logs y cache de Laravel
- Los archivos locales se sincronizan automáticamente

---

## 🎯 Deploy a Render

Una vez que todo funciona localmente con Docker:

1. Push a GitHub
2. Render detecta `Dockerfile` en `backend/`
3. Render construye la imagen automáticamente
4. Deploy iniciado

Ver: [RENDER_DOCKER.md](../RENDER_DOCKER.md) para instrucciones completas.

---

## 📚 Más información

- Dockerfile: `backend/Dockerfile`
- Docker Compose: `docker-compose.yml`
- Nginx config: `backend/nginx.conf.docker`
- Frontend dev: `frontend/Dockerfile.dev`
