FROM php:8.4-apache

# Install required system dependencies
RUN apt-get update && apt-get install -y \
    libonig-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Configure Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# Create a script to configure Apache port at runtime
RUN printf '#!/bin/bash\nif [ -n "$PORT" ]; then sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf; fi\nexec apache2-foreground\n' > /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE $PORT
