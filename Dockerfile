FROM php:8.2-cli

# Set working directory to axio2.0 where composer.json is located
WORKDIR /app

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy axio2.0 project
COPY axio2.0/ /app/

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 8080

# Start PHP server in backend directory
CMD ["php", "-S", "0.0.0.0:8080", "-t", "backend"]
