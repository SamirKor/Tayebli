FROM php:8.2-apache

# Install MySQLi extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy all files
COPY . /var/www/html/

# Start Apache
CMD ["apache2-foreground"]
