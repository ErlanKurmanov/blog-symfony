FROM php:8.3-fpm as php_base

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libicu-dev \
    libonig-dev \
    curl \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip intl ctype iconv exif opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/composer
ENV PATH=$PATH:/composer/vendor/bin

WORKDIR /var/www/html

# Development target
FROM php_base as development

# Install Node.js for development
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Set proper permissions for development
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Create necessary directories
RUN mkdir -p var/cache var/log public/uploads \
    && chown -R www-data:www-data var public \
    && chmod -R 775 var

EXPOSE 9000
CMD ["php-fpm"]

## Production target
#FROM php_base as production
#
## Copy composer files first for better caching
#COPY composer.json composer.lock ./
#RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader
#
## Install Node.js
#RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
#    && apt-get install -y nodejs
#
## Copy application files
#COPY . .
#
## Run composer and npm builds
#RUN composer dump-autoload --optimize && composer run-script auto-scripts
#RUN npm install && npm run build
#
## Set proper permissions
#RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public \
#    && chmod -R 775 /var/www/html/var
#
#EXPOSE 9000
#CMD ["php-fpm"]
