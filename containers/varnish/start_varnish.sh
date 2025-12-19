#!/bin/sh

set -x

echo "Setting up shutdown handler"
shutdown() {
    echo "Received shutdown signal, stopping varnish..."
    echo "Sending SIGTERM to varnish processes"
    killall -TERM varnishd 2>/dev/null || true
    killall -TERM varnishncsa 2>/dev/null || true
    echo "Waiting a moment for graceful shutdown"
    sleep 1
    echo "Force killing varnish processes if still running"
    killall -KILL varnishd 2>/dev/null || true
    killall -KILL varnishncsa 2>/dev/null || true
    exit 0
}

echo "Trap TERM and INT signals"
trap shutdown TERM INT

if false      ; then
  varnishd -j unix,user=vcache \
    -f /var/app/containers/varnish/config/default.vcl \
    -s malloc,${CACHE_SIZE} -a0.0.0.0:80 \
  && varnishncsa -a -c -w /var/log/varnish/access.log -D -P /run/varnishncsa.pid \
  && tail -f /var/log/varnish/access.log
else
  varnishd -j unix,user=vcache \
    -f /var/app/containers/varnish/config/default.vcl \
    -s malloc,${CACHE_SIZE} -a0.0.0.0:80

  varnishncsa -a -c -w /dev/null -P /run/varnishncsa.pid

  echo "Keeping the script running and waiting for signals"
  while true; do
    sleep 1
  done
fi



# && sleep 5 \
  # -S /etc/varnish/secret \

# tail -f /var/app/containers/varnish/up_notice.txt

