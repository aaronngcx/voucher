# Use the official PHP 8.2 image with FPM (FastCGI Process Manager)
FROM php:8.2-fpm

# Set the working directory inside the container
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer (Laravel's dependency manager)
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copy existing application files to the container
COPY . .

# Set proper permissions for storage and cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure storage and cache directories are always writable
RUN find /var/www/html/storage /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \; \
    && find /var/www/html/storage /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;

# Set the environment to development (change this to production if needed)
ENV APP_ENV=development

# Expose port 9000 and start PHP-FPM server
EXPOSE 9000
CMD ["php-fpm"]
