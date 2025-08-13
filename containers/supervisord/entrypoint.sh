
set -e
set -x



# There can be a race condition between the DB coming
# up, and us trying to use it. Explicitly waiting for it
# to be available prevents annoyance.
php cli.php db:wait_for_db

#/usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
/usr/bin/supervisord -n -c /var/app/containers/supervisord/supervisord.conf


#  /etc/supervisor/supervisord.conf
# COPY tasks/*.conf /etc/supervisor/conf.d/