FROM php:7.1.9-apache

RUN docker-php-ext-install pdo_mysql \
    # Enable apache module "rewrite", which provides a rule-based rewriting engine to rewrite requested URLs on the fly
    && a2enmod rewrite \ 
    # Enable apache module "headers", which provides customization of HTTP request and response headers
    && a2enmod headers \ 
    # Restarts the Apache httpd daemon
    && apachectl restart 

COPY apache.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html
