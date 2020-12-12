ARG BASE_IMAGE
FROM ${BASE_IMAGE}

RUN apt-get update && \
    apt-get install -y \
    libzip-dev \
    libcurl4-openssl-dev pkg-config libssl-dev \
    libxml2-dev \
    unzip librabbitmq-dev

RUN docker-php-ext-install sockets bcmath zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && pecl install amqp-1.10.2 \
    && docker-php-ext-enable amqp \
    && rm -rf /var/lib/apt/lists/* \
    && pecl clear-cache \
    && pecl config-set php_ini /usr/local/etc/php/conf.d/over.ini
