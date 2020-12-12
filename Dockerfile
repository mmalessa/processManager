ARG BASE_IMAGE
FROM ${BASE_IMAGE} AS sources

COPY --from=composer:2.0.7 /usr/bin/composer /usr/bin/
COPY . /application

WORKDIR "/application"
COPY .env .env.local

#RUN apt-get update && apt-get install -y git openssh-client
#
#RUN mkdir -p /root/.ssh && chmod 700 /root/.ssh
#
#ARG SSH_PRIVATE_KEY
#RUN echo "$SSH_PRIVATE_KEY" > ~/.ssh/id_rsa && \
#    chmod 600 ~/.ssh/id_rsa && \
#    ssh-keyscan gitlab.performance-media.pl > ~/.ssh/known_hosts

RUN composer install --no-interaction

FROM ${BASE_IMAGE}
WORKDIR "/application"
COPY --from=sources /application/ /application/
