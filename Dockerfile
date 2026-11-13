FROM php:8.2-apache

# Install system dependencies and PHP extension libraries
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) mysqli pdo_mysql gd zip \
    && a2enmod rewrite headers ssl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure Apache AllowOverride All
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy all application files
COPY . /var/www/html/

# Ensure start.sh has executable permissions and unix line endings
RUN chmod -R 755 /var/www/html \
    && chown -R www-data:www-data /var/www/html \
    && chmod +x /var/www/html/start.sh

# Render dynamically sets $PORT (defaults to 10000)
EXPOSE 10000

# Start Apache via start.sh
CMD ["/var/www/html/start.sh"]
