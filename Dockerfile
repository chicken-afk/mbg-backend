FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    build-essential \
    pkg-config \
    && docker-php-ext-install zip pdo_mysql pcntl

# Install Swoole manually without brotli
RUN curl -L -o swoole.tgz https://pecl.php.net/get/swoole && \
    tar -xzf swoole.tgz && \
    cd swoole-* && \
    phpize && \
    ./configure --enable-swoole --enable-openssl=no --enable-http2=no --enable-brotli=no && \
    make -j"$(nproc)" && \
    make install && \
    docker-php-ext-enable swoole

# Ensure pcntl is not disabled
RUN echo "disable_functions=" > /usr/local/etc/php/conf.d/disable_functions.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy Laravel app
COPY . .

# Install Laravel dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Expose port for Octane
EXPOSE 8000
CMD ["php", "artisan", "config:clear"]
CMD ["php", "artisan", "config:cache"]
CMD ["php", "artisan", "migrate"]
CMD ["php", "artisan", "db:seed"]

# Start Octane with Swoole
CMD ["sh", "-c", "php artisan config:clear && php artisan config:cache && php artisan migrate --force && php artisan db:seed --force && php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000"]

