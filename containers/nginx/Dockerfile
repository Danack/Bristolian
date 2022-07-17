FROM debian:10-slim

USER root

RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y nginx \
    ca-certificates wget curl git \
    lsb-release apt-transport-https gnupg

# Install 3rd party PHP 8.0 packages
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list

RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg

RUN  apt-get update -qq &&  \
  apt-get dist-upgrade -y && \
  DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y php8.0 php8.0-common php8.0-cli php8.0-fpm \
  php8.0-mysql php8.0-curl php8.0-xml php8.0-mbstring \
  php8.0-intl php8.0-redis php8.0-zip

WORKDIR /var/app

CMD sh /var/app/containers/nginx/entrypoint.sh

# For debugging, rather than having the container exit immediately,
# being able to switch the command to be just 'follow that file' allows
# you to bash into the container to inspect the state after nginx has crashed.
# CMD ["tail", "-f", "/var/app/readme.MD"]