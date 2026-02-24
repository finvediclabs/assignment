# PHP 8.2 with Apache web server
FROM php:8.2-apache

# Install database drivers
RUN docker-php-ext-install pdo_mysql mysqli

# Copy source code to container web root
COPY ./ /var/www/html/

# Set permissions for the web user
RUN chown -R www-data:www-data /var/www/html

# Enable Apache mod_rewrite
RUN a2enmod rewrite
