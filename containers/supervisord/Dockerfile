FROM debian:11

USER root

# Get Debian up-to-date
RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
    mariadb-client wget curl unzip \
    ca-certificates lsb-release apt-transport-https gnupg

RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list \
    && curl https://packages.sury.org/php/apt.gpg | apt-key add - \
    && apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y php8.2 php8.2-common php8.2-cli \
    php8.2-mysql php8.2-curl php8.2-xml php8.2-mbstring \
    php8.2-intl php8.2-redis php8.2-zip \
    supervisor

WORKDIR /var/app

RUN mkdir -p /var/log/supervisor

#COPY supervisord.conf /etc/supervisor/supervisord.conf
#COPY tasks/*.conf /etc/supervisor/conf.d/

CMD tail -f README.md
# CMD sh /var/app/containers/supervisord/entrypoint.sh


