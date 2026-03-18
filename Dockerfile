FROM php:8.2-cli

# Set working directory
WORKDIR /app

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy entire project
COPY . /app

# Install PHP dependencies
WORKDIR /app/backend
RUN composer install --no-dev --optimize-autoloader

# Back to app root
WORKDIR /app

# Expose port
EXPOSE 8080

# Start PHP server in backend directory
CMD ["php", "-S", "0.0.0.0:8080", "-t", "backend"]
