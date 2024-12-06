FROM debian:11-slim

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
     DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y php8.2 php8.2-common php8.2-cli \
     php8.2-mysql php8.2-curl php8.2-xml php8.2-mbstring \
     php8.2-intl php8.2-redis php8.2-zip unzip

# Make the default directory be the useful one
WORKDIR /var/app

# CMD tail -f /var/app/readme.MD

CMD sh /var/app/containers/installer/entrypoint.sh
