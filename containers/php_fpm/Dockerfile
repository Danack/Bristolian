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
     php8.2 php8.2-common php8.2-cli php8.2-fpm php8.2-dev \
     php8.2-mysql php8.2-curl php8.2-imagick php8.2-xml php8.2-mbstring \
     php8.2-intl php8.2-redis php8.2-zip \
     build-essential make gcc g++

# WORKDIR /var/app

# Copy pie.phar from container directory and install the extension
# COPY pie.phar /var/app/pie.phar
RUN php pie.phar install danack/fnbind:@alpha

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
     php8.2 php8.2-common php8.2-cli php8.2-fpm \
     php8.2-mysql php8.2-curl php8.2-imagick php8.2-xml php8.2-mbstring \
     php8.2-intl php8.2-redis php8.2-zip

WORKDIR /var/app

# Copy the compiled extension from the builder stage
COPY --from=builder /usr/lib/php/20220829/fnbind.so /usr/lib/php/20220829/fnbind.so

# Enable the extension
RUN echo "extension=fnbind.so" > /etc/php/8.2/mods-available/fnbind.ini \
    && phpenmod fnbind

RUN usermod -u 1000 www-data

RUN echo 'alias cli="php cli.php"' >> ~/.bashrc

CMD sh /var/app/containers/php_fpm/entrypoint.sh