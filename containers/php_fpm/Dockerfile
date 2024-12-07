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
     php8.2 php8.2-common php8.2-cli php8.2-fpm \
     php8.2-mysql php8.2-curl php8.2-xml php8.2-mbstring \
     php8.2-intl php8.2-redis php8.2-zip

# Make the default directory you
WORKDIR /var/app

#RUN groupadd -g 999 appuser && \
#    useradd -r -u 999 -g appuser appuser
#USER appuser

#CMD tail -f README.md

RUN usermod -u 1000 www-data

RUN echo 'alias cli="php cli.php"' >> ~/.bashrc

CMD sh /var/app/containers/php_fpm/entrypoint.sh