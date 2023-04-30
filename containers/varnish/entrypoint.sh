
set -e
set -x


ENV_TO_USE=${ENV_DESCRIPTION:=default}

echo "ENV_TO_USE is ${ENV_TO_USE}";


## Generate nginx config file for the centos,dev environment
#php vendor/bin/configurate \
#    -p config.source.php \
#    containers/varnish/config/default.vcl.php \
#    containers/varnish/config/default.vcl \
#    $ENV_TO_USE

sh /var/app/containers/varnish/start_varnish.sh
