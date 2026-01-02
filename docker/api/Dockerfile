FROM dunglas/frankenphp:php8.4-alpine

# Install PHP extensions
RUN install-php-extensions \
	pdo_pgsql \
	gd \
	amqp \
	xdebug \
	intl \
	zip \
	opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files
COPY api/composer.json api/composer.lock ./

# Install dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy source files
COPY api/ .

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Expose ports
EXPOSE 80 443

# Start FrankenPHP
CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]
