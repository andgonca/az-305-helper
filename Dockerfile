FROM php:8.2-apache

# Enable Apache modules
RUN a2enmod rewrite
RUN a2enmod headers

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY public/ .
COPY api/ ./api/
COPY src/ ./src/
COPY data/ ./data/
COPY config.php .

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN chmod -R 755 /var/www/html/data

# Configure Apache
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Set environment variables
ENV PHP_VERSION=8.2
ENV APP_ENV=production

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/api/domains || exit 1

EXPOSE 80
CMD ["apache2-foreground"]
