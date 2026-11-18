FROM php:8.3-cli-alpine

# Install system dependencies & SQLite
RUN apk add --no-cache \
    curl \
    git \
    libzip-dev \
    libpng-dev \
    sqlite-dev \
    oniguruma-dev \
    zip \
    unzip

# Install required PHP extensions for Laravel & SQLite
RUN docker-php-ext-install \
    pdo_sqlite \
    mbstring \
    zip \
    gd \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . .

# Set up required SQLite database & storage directories with proper permissions
RUN mkdir -p database \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && touch database/database.sqlite \
    && chmod -R 777 storage bootstrap/cache database

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Cache configuration, routes and views
RUN php artisan config:clear && \
    php artisan route:cache && \
    php artisan view:cache

# Render automatically supplies the PORT environment variable
ENV PORT=10000
EXPOSE 10000

# Start Laravel: ensure sqlite file permissions and run migrations before serving
CMD ["sh", "-c", "touch database/database.sqlite && chmod 777 database/database.sqlite && php artisan migrate --force 2>/dev/null; php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
