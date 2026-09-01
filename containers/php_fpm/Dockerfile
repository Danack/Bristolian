# Stage 1: Builder stage with dev dependencies
FROM debian:11-slim AS builder

USER root

# Get Debian up-to-date
RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
    mariadb-client wget curl unzip \
    ca-certificates lsb-release apt-transport-https gnupg

# Install 3rd party PHP packages
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list \
    && curl https://packages.sury.org/php/apt.gpg | apt-key add -

# Install PHP with dev dependencies and build tools for building extensions
RUN  apt-get update -qq && \
     apt-get dist-upgrade -y && \
     DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y \
     php8.4 php8.4-common php8.4-cli php8.4-fpm php8.4-dev \
     php8.4-mysql php8.4-curl php8.4-imagick php8.4-xml php8.4-mbstring \
     php8.4-intl php8.4-redis php8.4-zip php8.4-pcov \
     build-essential make gcc g++

WORKDIR /tmp

# Copy pie.phar from container directory and install the extension
COPY pie.phar /tmp/pie.phar
RUN php pie.phar install danack/fnbind:@alpha

RUN ls -l /usr/lib/php
RUN ls -l /usr/lib/php
RUN ls -l /usr/lib/php

# Stage 2: Runtime stage without dev dependencies
FROM debian:11-slim AS runtime

USER root

# Get Debian up-to-date
RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
    mariadb-client wget curl unzip \
    ca-certificates lsb-release apt-transport-https gnupg

# Install 3rd party PHP packages
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list \
    && curl https://packages.sury.org/php/apt.gpg | apt-key add -

# Install PHP without dev dependencies (cleaner, smaller image)
RUN  apt-get update -qq && \
     apt-get dist-upgrade -y && \
     DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y \
     php8.4 php8.4-common php8.4-cli php8.4-fpm \
     php8.4-mysql php8.4-curl php8.4-imagick php8.4-xml php8.4-mbstring \
     php8.4-intl php8.4-redis php8.4-zip php8.4-pcov

WORKDIR /var/app

# Copy the compiled extension from the builder stage
COPY --from=builder /usr/lib/php/20240924/fnbind.so /usr/lib/php/20240924/fnbind.so

# Enable the extension
RUN echo "extension=fnbind.so" > /etc/php/8.4/mods-available/fnbind.ini \
    && phpenmod fnbind

RUN usermod -u 1000 www-data

RUN echo 'alias cli="php cli.php"' >> ~/.bashrc

# We set the title of the terminal window.
COPY iterm-title.sh /usr/local/bin/iterm-title.sh
RUN chmod +x /usr/local/bin/iterm-title.sh
RUN echo 'source /usr/local/bin/iterm-title.sh' >> ~/.bashrc

CMD ["sh", "/var/app/containers/php_fpm/entrypoint.sh"]