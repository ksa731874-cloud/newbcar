FROM php:8.4-apache

# Install required system dependencies
RUN apt-get update && apt-get install -y \
    libonig-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring

# Fix Apache MPM configuration - disable all MPM modules except prefork
RUN a2dismod mpm_event mpm_worker mpm_itk 2>/dev/null || true && \
    a2enmod mpm_prefork

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Configure Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# Create a script to configure Apache port at runtime
RUN printf '#!/bin/bash\nif [ -n "$PORT" ]; then\n  sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf\n  sed -i "s/<VirtualHost \\*:80>/<VirtualHost *:$PORT>/g" /etc/apache2/sites-available/000-default.conf\nfi\nexec apache2-foreground\n' > /entrypoint.sh && chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
