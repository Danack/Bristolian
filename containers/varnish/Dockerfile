FROM debian:11-slim



USER root

# Get Debian up-to-date
# RUN apt-get update -qq \
#     && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
#     wget curl varnish libmaxminddb0 psmisc \
#     ca-certificates lsb-release apt-transport-https gnupg bsdmainutils
#
#
# RUN apt-key del B188E2B695BD4743
# RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
#
# RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list


# Get Debian up-to-date
RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    git wget curl varnish libmaxminddb0 psmisc \
    ca-certificates lsb-release apt-transport-https gnupg bsdmainutils

# Install 3rd party PHP packages
# RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list
# RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg

# RUN  apt-get update -qq && \
#      apt-get dist-upgrade -y && \
#      DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y \
#      php8.2 php8.2-common php8.2-cli \
#      php8.2-mbstring \
#      php8.2-intl php8.2-zip

# RUN  apt-get update -qq && \
#   apt-get dist-upgrade -y && \
#   DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y \
#   git php8.0 \
#   php8.0-common php8.0-cli \
#   php8.0-mysql php8.0-curl php8.0-xml php8.0-mbstring \
#   php8.0-intl php8.0-redis php8.0-zip


# Make the default directory you sh into
WORKDIR /var/app

COPY lib/libvmod_geoip2.la /usr/lib/x86_64-linux-gnu/varnish/vmods/libvmod_geoip2.la
COPY lib/libvmod_geoip2.so /usr/lib/x86_64-linux-gnu/varnish/vmods/libvmod_geoip2.so

CMD sh /var/app/containers/varnish/entrypoint.sh
