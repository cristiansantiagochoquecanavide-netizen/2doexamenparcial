FROM php:8.2-cli

WORKDIR /app

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    postgresql-client \
    npm \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar archivos del proyecto
COPY . .

# Crear directorios necesarios
RUN mkdir -p storage/logs bootstrap/cache && chmod -R 777 storage bootstrap

# Instalar dependencias PHP (sin ejecutar migraciones)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Instalar y compilar frontend
RUN npm install && npm run build

# Hacer ejecutables los scripts
RUN chmod +x start-server.sh init-database.sh

# Exponer puerto
EXPOSE 10000

# Comando de inicio
CMD ["bash", "start-server.sh"]
