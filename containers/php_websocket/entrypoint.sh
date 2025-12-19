
set -e
set -x

# tail -f /var/app/containers/php_websocket/readme.MD

cd /var/app/chat/src

exec php index.php

