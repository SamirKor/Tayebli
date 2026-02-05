# Use official PHP Apache image
FROM php:8.2-apache

# Copy all your files to Apache web directory
COPY . /var/www/html/

# Set proper permissions
RUN chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
