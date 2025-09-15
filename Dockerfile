FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions (including SOAP and SQLite)
RUN apk add --no-cache \
    git \
    unzip \
    oniguruma-dev \
    icu-dev \
    libzip-dev \
    libxml2-dev \
    sqlite-dev \
    bash \
  && docker-php-ext-configure intl \
  && docker-php-ext-install -j"$(nproc)" \
    intl \
    mbstring \
    zip \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    xml \
    soap \
    pcntl \
    opcache \
  && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Leverage Docker layer cache for dependencies when possible
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts || true

# Copy the rest of the application
COPY . .

# Ensure writable directories
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9986
CMD ["php-fpm"]


