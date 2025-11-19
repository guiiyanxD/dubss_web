#!/bin/bash
set -e

echo "🚀 Starting Laravel DUBSS Application..."

# Crear directorios necesarios
mkdir -p /tmp/opcache
mkdir -p /var/log/supervisor
mkdir -p /var/log/nginx
mkdir -p /var/log/php

# Esperar a que PostgreSQL esté disponible (si está en Cloud SQL)
if [ ! -z "$DB_HOST" ]; then
    echo "⏳ Waiting for PostgreSQL..."
    timeout 60 bash -c 'until pg_isready -h $DB_HOST -p ${DB_PORT:-5432} -U $DB_USERNAME; do sleep 2; done' || {
        echo "❌ PostgreSQL connection timeout"
        exit 1
    }
    echo "✅ PostgreSQL is ready"
fi

# Ejecutar migraciones (solo si la variable está activada)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "🔄 Running database migrations..."
    php artisan migrate --force --no-interaction
    echo "✅ Migrations completed"
fi

# Ejecutar seeders (solo si la variable está activada)
if [ "$RUN_SEEDERS" = "true" ]; then
    echo "🌱 Running database seeders..."
    php artisan db:seed --force --no-interaction
    echo "✅ Seeders completed"
fi

# Limpiar caché si es necesario
if [ "$CLEAR_CACHE" = "true" ]; then
    echo "🧹 Clearing cache..."
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    echo "✅ Cache cleared"
fi

# Optimizar aplicación
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Crear enlace simbólico de storage (si no existe)
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
fi

# Verificar permisos
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

# Iniciar supervisor
echo "✅ Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
