FROM php:8.2-cli

# Set working directory
WORKDIR /app

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy entire project including composer.json
COPY . /app

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 8080

# Start PHP server at root directory (index.php is the router)
CMD ["php", "-S", "0.0.0.0:8080"]
