
# Stage 1: Dependencies & build
FROM php:8.2-fpm-alpine AS builder

# Install system packages
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    zlib-dev \
  && docker-php-ext-install pdo_mysql zip mbstring gd

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy project files and install PHP dependencies (if any)
WORKDIR /app
COPY src/ /app/src/
COPY api/ /app/api/
COPY db_link/ /app/db_link/
COPY test/ /app/test/
COPY info.php index.html signup view.php /app/

# If in future this project uses a composer.json or something like that ..., uncomment:
# COPY composer.json composer.lock /app/
# RUN composer install --no-dev --optimize-autoloader

# Stage 2: Production image
FROM php:8.2-fpm-alpine

# Create non-root user
RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www

# Copy PHP extensions and application from builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer
COPY --from=builder /app /var/www/html

# Set permissions
RUN chown -R www:www /var/www/html

# Configure PHP-FPM
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Expose port and switch to non-root
WORKDIR /var/www/html
USER www
EXPOSE 9000
CMD ["php-fpm"]