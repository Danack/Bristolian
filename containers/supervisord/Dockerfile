FROM debian:9

USER root

# Get Debian up-to-date
RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
    mariadb-client wget curl \
    ca-certificates lsb-release apt-transport-https gnupg bsdmainutils

RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list \
    && curl https://packages.sury.org/php/apt.gpg | apt-key add - \
    && apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y php8.0 php8.0-common php8.0-cli \
    php8.0-mysql php8.0-curl php8.0-xml php8.0-mbstring \
    php8.0-intl php8.0-redis php8.0-zip \
    supervisor

WORKDIR /var/app

RUN mkdir -p /var/log/supervisor

COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY tasks/*.conf /etc/supervisor/conf.d/

# CMD tail -f README.md
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
