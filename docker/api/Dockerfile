FROM dunglas/frankenphp:php8.4-alpine

# add additional extensions here:
RUN install-php-extensions \
	pdo_pgsql \
	gd \
	amqp \
	xdebug \
	intl \
	zip \
	opcache
