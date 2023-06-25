
set -e
set -x

echo never > /sys/kernel/mm/transparent_hugepage/enabled

#if ! grep -q never "/sys/kernel/mm/transparent_hugepage/enabled"; then
#  echo never > /sys/kernel/mm/transparent_hugepage/enabled
#fi

CMD ["redis-server"]