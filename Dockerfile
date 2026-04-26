# ============================================
# Dockerfile para Laravel + Vue/Inertia
# Optimizado para Render Free Tier
# ============================================

FROM php:8.2-apache

# Instalar extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    xml \
    bcmath \
    curl \
    zip \
    gd \
    opcache \
    pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar OPcache para producción
RUN echo "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=10000\n\
opcache.validate_timestamps=0\n\
opcache.save_comments=1\n\
opcache.fast_shutdown=1" > /usr/local/etc/php/conf.d/opcache.ini

# Configurar PHP para producción
RUN echo "memory_limit=256M\n\
upload_max_filesize=10M\n\
post_max_size=12M\n\
max_execution_time=60\n\
expose_php=Off" > /usr/local/etc/php/conf.d/production.ini

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar configuración de Apache
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar composer files primero (para cachear dependencias)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copiar package files y construir assets
COPY package.json package-lock.json ./
RUN npm ci --omit=dev

# Copiar el resto del proyecto
COPY . .

# Finalizar instalación de Composer
RUN composer dump-autoload --optimize \
    && composer run-script post-autoload-dump

# Build de assets con Vite
RUN npm run build && rm -rf node_modules

# Permisos para storage y cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar y dar permisos al script de inicio
COPY scripts/render-build.sh /usr/local/bin/render-build.sh
RUN chmod +x /usr/local/bin/render-build.sh

# Render usa la variable PORT
ENV PORT=10000
RUN sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf

EXPOSE ${PORT}

# Ejecutar migraciones y luego iniciar Apache
CMD ["/bin/bash", "-c", "/usr/local/bin/render-build.sh && apache2-foreground"]
