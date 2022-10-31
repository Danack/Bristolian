FROM debian:10-slim

USER root

# Get Debian up-to-date
RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
    mariadb-client wget curl \
    ca-certificates lsb-release apt-transport-https gnupg

RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list

RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg

RUN  apt-get update -qq && \
     apt-get dist-upgrade -y && \
     DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y php8.1 php8.1-common php8.1-cli \
     php8.1-mysql php8.1-curl php8.1-xml php8.1-mbstring \
     php8.1-intl php8.1-redis php8.1-zip unzip

# Make the default directory be the useful one
WORKDIR /var/app

# CMD tail -f /var/app/readme.MD

CMD sh /var/app/containers/installer/entrypoint.sh
