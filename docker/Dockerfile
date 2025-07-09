FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    supervisor \
    procps \
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
    cron \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip intl ctype iconv exif opcache

# Create Supervisor directories
RUN mkdir -p /var/log/supervisor /etc/supervisor/conf.d

# Install the Redis extension using PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Node.js for development
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/composer
ENV PATH=$PATH:/composer/vendor/bin

WORKDIR /var/www/html

# Set proper permissions for development
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Create necessary directories
RUN mkdir -p var/cache var/log public/uploads \
    && chown -R www-data:www-data var public \
    && chmod -R 775 var

# Copy Supervisor config files
COPY ./docker/supervisord.conf /etc/supervisord.conf
# Copy all supervisor program configs
COPY ./docker/supervisor/*.conf /etc/supervisor/conf.d/

COPY ./docker/crontab /etc/cron.d/my-cron
RUN chmod 0644 /etc/cron.d/my-cron

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

EXPOSE 9000
