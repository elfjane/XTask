FROM php:8.4-fpm AS base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

WORKDIR /var/www

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- Development Stage ---
FROM base AS development
USER www-data
CMD ["php-fpm"]

# --- Production Stage ---
FROM base AS production
COPY --chown=www-data:www-data . /var/www
RUN composer install --no-dev --optimize-autoloader --no-interaction
USER www-data
CMD ["php-fpm"]
