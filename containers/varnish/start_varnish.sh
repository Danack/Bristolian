#!/bin/sh

set -e
set -x

#varnishd -j unix,user=vcache \
#  -f /var/app/containers/varnish/config/default.vcl \
#  -s malloc,${CACHE_SIZE} -a0.0.0.0:80 \
#&& varnishncsa -a -c -w /var/log/varnish/access.log -D -P /run/varnishncsa.pid \
#&& tail -f /var/log/varnish/access.log
#
#

varnishd -j unix,user=vcache \
  -f /var/app/containers/varnish/config/default.vcl \
  -s malloc,${CACHE_SIZE} -a0.0.0.0:80 \


varnishncsa -a -c -w /dev/null -P /run/varnishncsa.pid



# && sleep 5 \
  # -S /etc/varnish/secret \

# tail -f /var/app/containers/varnish/up_notice.txt

