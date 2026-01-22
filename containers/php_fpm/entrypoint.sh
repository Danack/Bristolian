
set -e
set -x

# tail -f /var/app/README.md

# Determine if we're in local development environment
# Check ENV_DESCRIPTION environment variable or config.generated.php
ENV_DESCRIPTION=${ENV_DESCRIPTION:-default}
IS_LOCAL=false

if [ "$ENV_DESCRIPTION" = "local" ]; then
    IS_LOCAL=true
elif [ -f "/var/app/config.generated.php" ]; then
    # Check if config file indicates local environment
    if grep -q "'bristol_org.env' => 'local'" /var/app/config.generated.php 2>/dev/null; then
        IS_LOCAL=true
    fi
fi

# Conditionally enable/disable pcov based on environment
if [ "$IS_LOCAL" = "true" ]; then
    # Enable pcov for local development
    # Copy our custom pcov.ini which loads the extension and sets our custom settings
    if [ -f "/var/app/containers/php_fpm/pcov.ini" ]; then
        cp /var/app/containers/php_fpm/pcov.ini /etc/php/8.2/fpm/conf.d/20-pcov.ini
        echo "pcov enabled for local development"
    else
        # Fallback: enable via phpenmod if custom ini doesn't exist
        phpenmod -v 8.2 -s fpm pcov 2>/dev/null || true
        echo "pcov enabled for local development (using system default)"
    fi
else
    # Disable pcov for production
    # Remove any pcov configuration files
    rm -f /etc/php/8.2/fpm/conf.d/20-pcov.ini
    phpdismod -v 8.2 -s fpm pcov 2>/dev/null || true
    echo "pcov disabled for production"
fi

exec /usr/sbin/php-fpm8.2 \
  --nodaemonize \
  --fpm-config=/var/app/containers/php_fpm/config/fpm.conf \
  -c /var/app/containers/php_fpm/config/php.ini