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


RUN apt install -y \
  libgl1 \
  libglib2.0-0 \
  libsm6 \
  libxext6 \
  libxrender1 \
  python3-pip

# Attempt to align user id's across containers
RUN usermod -u 1000 www-data
USER www-data


# 2. Install EasyOCR (this will pull torch + models)
RUN pip install --no-cache-dir easyocr

#  pre-download models at build time
#  (avoids first-run network access)
COPY easyocr_install.sh /tmp/easyocr_install.sh
# RUN sh /tmp/easyocr_install.sh

WORKDIR /var/app


RUN mkdir -p /var/log/supervisor


# CMD tail -f /var/app/containers/supervisord/README.md
CMD sh /var/app/containers/supervisord/entrypoint.sh

