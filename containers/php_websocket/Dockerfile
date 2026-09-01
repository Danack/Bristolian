FROM debian:11-slim

USER root

# Get Debian up-to-date
RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
    mariadb-client wget curl unzip \
    ca-certificates lsb-release apt-transport-https gnupg



# Install 3rd party PHP packages
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list \
    && curl https://packages.sury.org/php/apt.gpg | apt-key add -



RUN  apt-get update -qq && \
     apt-get dist-upgrade -y && \
     DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y \
     php8.4 php8.4-common php8.4-cli php8.4-fpm \
     php8.4-mysql php8.4-curl php8.4-imagick php8.4-xml php8.4-mbstring \
     php8.4-intl php8.4-redis php8.4-zip

# Make the default directory you
WORKDIR /var/app

RUN usermod -u 1000 www-data

RUN echo 'alias cli="php cli.php"' >> ~/.bashrc

# CMD ["sh", "/var/app/containers/php_websocket/entrypoint.sh"]

CMD ["sh", "/var/app/containers/php_websocket/entrypoint_dev_watch.sh"]

# CMD tail -f /var/app/containers/php_websocket/readme.MD