ARG BASE_IMAGE
FROM ${BASE_IMAGE}

ARG COMPOSER_VERSION=2.0.7

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=$COMPOSER_VERSION \
    && apt-get update && apt-get install -y git vim \
    && cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    && rm -rf /var/lib/apt/lists/* \
    && pecl config-set php_ini /usr/local/etc/php/conf.d/over.ini

RUN echo "su - developer" > /root/.bashrc
ARG DEVELOPER_UID
RUN useradd -m -s /bin/bash -u ${DEVELOPER_UID} developer
USER developer
WORKDIR "/application"
