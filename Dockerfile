# PHP 8.4 requerido por las dependencias bloqueadas en composer.lock
FROM php:8.4-fpm-bookworm

# Evitamos que las instalaciones pidan interacción (ahorra tiempo y procesos)
ENV DEBIAN_FRONTEND=noninteractive

# Instalamos todo en un solo bloque para reducir el peso de la imagen
RUN apt-get update && apt-get install -y \
 git \
 unzip \
 libzip-dev \
 libpng-dev \
 zip \
 curl \
 ca-certificates \
 gnupg \
 python3 \
 python3-pip \
 && docker-php-ext-install pdo pdo_mysql zip gd \
 # Limpieza inmediata de archivos temporales
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# IA: Instalación con flags para no guardar basura en el disco (no-cache-dir)
RUN pip3 install pandas numpy scikit-learn --break-system-packages --no-cache-dir

# Node.js 18 LTS
RUN mkdir -p /etc/apt/keyrings \
 && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
 && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_18.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
 && apt-get update \
 && apt-get install -y nodejs

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Instalamos dependencias de JS
COPY package*.json ./
RUN npm install --quiet

# Copy application code
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Fix para Laravel 12 + PHP 8.4
RUN sed -i 's/return \$port + \$this->portOffset;/return (int)\$port + \$this->portOffset;/' \
    vendor/laravel/framework/src/Illuminate/Foundation/Console/ServeCommand.php

# Generate Laravel app key if not exists
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Build frontend assets
RUN npm run build

EXPOSE 8000

CMD sh -c "php -S 0.0.0.0:$PORT -t public"