<?php

function createServerBlock(
    array $portStrings,
    array $domains,
    string $root,
    string $indexFilename,
    string $phpBackend,
    string $description
) {

  $portsInfo = '';
  foreach ($portStrings as $portString) {
      $portsInfo .= "        listen $portString;\n";
  }

  $domainInfo = implode(" ", $domains);

  $output = <<< CONFIG
  
    # $description
  
    server {
        server_name $domainInfo;
$portsInfo
        root $root;

        location ~* ^(.+).(ttf)$ {
            access_log on;
            try_files \$uri /$indexFilename?file=$1.$2&q=\$uri&\$args;
            add_header Pragma public;
            add_header Cache-Control "public";
            add_header Cache-Control "no-transform";
            add_header Cache-Control "max-age=86400";
            add_header Cache-Control "s-maxage=7200";
            #add_header XDJA "ttfblock";
        }

        location ~* ^(.+).(bmp|bz2|css|gif|doc|gz|html|ico|jpg|jpeg|js|map|mid|midi|pcap|png|rtf|rar|pdf|ppt|tar|tgz|txt|wav|xls|zip)$ {
            #access_log off;
        try_files \$uri /$indexFilename?file=$1.$2&q=\$uri&\$args;
            expires 20m;
            add_header Pragma public;
            add_header Cache-Control "public";
            add_header Cache-Control "no-transform";
            add_header Cache-Control "max-age=1200";
            add_header Cache-Control "s-maxage=300";
            #add_header XDJA "otherblock";
        }

        location / {
            try_files \$uri \$uri.php \$uri/index.php /$indexFilename?q=\$uri&\$args;
            fastcgi_param HTTP_PROXY "";
            include /var/app/containers/nginx/config/fastcgi.conf;
            fastcgi_param SCRIPT_FILENAME \$document_root/\$fastcgi_script_name;
            fastcgi_read_timeout 300;
            fastcgi_pass php_fpm:9000;
            add_header DJA "php_file";
        }

        location /$indexFilename {
            # Mitigate https://httpoxy.org/ vulnerabilities
            fastcgi_param HTTP_PROXY "";
            include /var/app/containers/nginx/config/fastcgi.conf;
            fastcgi_param SCRIPT_FILENAME \$document_root/\$fastcgi_script_name;
            fastcgi_read_timeout 300;
            fastcgi_pass $phpBackend;
            #add_header DJA "php_front_controller";
        }
    }
CONFIG;

  return $output;
}


$apiNormalBlock = createServerBlock(
    $portStrings   = ['80', '8000'],
    $domains = [
        '*.api.bristolian.org',
        'api.bristolian.org'
    ],
    $root = '/var/app/api/public',
    $indexFilename = 'index.php',
    $phpBackend = 'php_fpm:9000',
    'api normal block'
);

$apiDebugBlock = createServerBlock(
    $portStrings   = ['8001'],
    $domains = [
        '*.api.bristolian.org',
        'api.bristolian.org'
    ],
    $root = '/var/app/api/public',
    $indexFilename = 'index.php',
    $phpBackend = 'php_fpm_debug:9000',
    'api debug block'
);


$appNormalBlock = createServerBlock(
    $portStrings   = ['80', '8000'],
    $domains = [
        '*.bristolian.org',
        'bristolian.org',
    ],
    $root = '/var/app/app/public',
    $indexFilename = 'index.php',
    $phpBackend = 'php_fpm:9000',
    'app normal block'
);

$appDebugBlock = createServerBlock(
    $portStrings   = ['8001'],
    $domains = [
        '*.bristolian.org',
        'bristolian.org',
    ],
    $root = '/var/app/app/public',
    $indexFilename = 'index.php',
    $phpBackend = 'php_fpm_debug:9000',
    'app debug block'
);

if (${'system.build_debug_php_containers'} === false ||
    ${'system.build_debug_php_containers'} === 'false') {
    $appDebugBlock = '';
    $apiDebugBlock = '';
}


$output = <<< OUTPUT

user www-data;
worker_processes auto;
pid /run/nginx.pid;
#include /etc/nginx/modules-enabled/*.conf;
daemon off;

events {
    worker_connections 768;
    # multi_accept on;
}

http {
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 10m;
    server_tokens off;

    include /var/app/containers/nginx/config/mime.types;
    default_type application/octet-stream;


log_format main '\$http_x_real_ip - \$remote_user [\$time_local] '
    '"\$request" \$status \$body_bytes_sent '
    '"\$http_referer" "\$http_user_agent"';


    access_log /dev/stdout main;
    # access_log off;
    error_log /dev/stderr;

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    #Set what content types may be gzipped.
    gzip_types text/plain text/css application/json application/javascript application/x-javascript text/javascript text/xml application/xml application/rss+xml application/atom+xml application/rdf+xml;


$apiNormalBlock

$apiDebugBlock

$appNormalBlock

$appDebugBlock

}

OUTPUT;

return $output;


