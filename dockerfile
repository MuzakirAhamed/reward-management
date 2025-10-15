# ===============================
# Stage 1 - Build Frontend (Vite)
# ===============================
FROM node:18 AS frontend
WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm install

# Copy all files and build the frontend
COPY . .
RUN npm run build


# =======================================
# Stage 2 - Backend (Laravel + PHP + Composer)
# =======================================
FROM php:8.2-fpm AS backend

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    git curl unzip libpng-dev libonig-dev libxml2-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set the working directory inside the container
WORKDIR /var/www

# Copy Laravel files (backend)
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/dist ./public/dist

# Install Laravel dependencies (optimized for production)
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader

# Fix permissions for Render’s environment
RUN chown -R www-data:www-data /var/www

# Set environment to production
ENV APP_ENV=production
ENV APP_DEBUG=false

# Expose port 8000 (Render listens here)
EXPOSE 8000

# Run Laravel server (Render automatically maps this port)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
