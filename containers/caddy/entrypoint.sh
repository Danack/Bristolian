
set -e
set -x

ENV_TO_USE=${ENV_DESCRIPTION:=default}
echo "ENV_TO_USE is ${ENV_TO_USE}";


# tail -f /var/app/readme.MD
/usr/bin/caddy run --config /var/app/containers/caddy/Caddyfile

# /usr/bin/caddy fmt /var/app/containers/caddy/Caddyfile
