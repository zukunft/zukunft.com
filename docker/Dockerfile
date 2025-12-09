# Dockerfile
FROM php:8.2-apache

# Install required packages
RUN apt-get update && apt-get install -y \
    micro \
    libpq-dev \
    libyaml-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql curl \
    && pecl install yaml \
    && docker-php-ext-enable yaml

# clean up apt cache
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/*
    
# Expose port 8080 and Start the Apache server
EXPOSE 8080
CMD ["apache2-foreground"]
