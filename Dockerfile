# ============================================
# STAGE 1: Build Assets (Node.js + Vite)
# ============================================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copiar package files
COPY package*.json ./
COPY vite.config.js ./
COPY postcss.config.js ./
COPY tailwind.config.js ./

# Instalar dependencias de Node
RUN npm ci --production=false

# Copiar recursos necesarios para build
COPY resources ./resources
COPY public ./public

# Build de assets con Vite
RUN npm run build

# ============================================
# STAGE 2: PHP Dependencies
# ============================================
FROM composer:2.6 AS composer-builder

WORKDIR /app

# Copiar archivos de composer
COPY composer.json composer.lock ./

# Instalar dependencias de PHP (sin dev)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --no-plugins \
    --prefer-dist \
    --optimize-autoloader

# ============================================
# STAGE 3: Runtime Image
# ============================================
FROM php:8.3-fpm-alpine

# Argumentos de build
ARG BUILD_DATE
ARG VCS_REF

# Labels para metadata
LABEL maintainer="DUBSS Team" \
      org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.schema-version="1.0"

# Instalar dependencias del sistema Y dependencias de desarrollo
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    postgresql-client \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    curl \
    git \
    bash \
    autoconf \
    g++ \
    make \
    && rm -rf /var/cache/apk/*

# Instalar extensiones PHP requeridas
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        intl \
        mbstring \
        opcache \
        pcntl \
        bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

# Configurar PHP para producción
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Configurar Nginx
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configurar Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Crear usuario y grupo para Laravel
RUN addgroup -g 1000 laravel \
    && adduser -D -u 1000 -G laravel laravel

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar aplicación Laravel
COPY --chown=laravel:laravel . .

# Copiar vendor desde composer-builder
COPY --from=composer-builder --chown=laravel:laravel /app/vendor ./vendor

# Copiar assets compilados desde node-builder
COPY --from=node-builder --chown=laravel:laravel /app/public/build ./public/build

# Crear directorios necesarios con permisos correctos
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R laravel:laravel storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Optimizar Laravel para producción
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

# Exponer puerto 8080 (requerido por Cloud Run)
EXPOSE 8080

# Script de inicio
COPY docker/start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start


# Cambiar a usuario no-root
USER laravel

# Comando de inicio
CMD ["/usr/local/bin/start"]
