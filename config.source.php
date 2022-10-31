<?php

declare(strict_types=1);

use Bristolian\Config;

$sha = `git rev-parse HEAD`;

if ($sha === null) {
    echo "Failed to read sha from git. Is git installed in container?";
    exit(-1);
}

$sha = trim($sha);


// Default settings
$default = [
    'varnish.pass_all_requests' => false,
    'varnish.allow_non_ssl' => false,
    'system.build_debug_php_containers' => false,
    'php.memory_limit' => getenv('php.memory_limit') ?: '64M',
    'php.web.processes' => 20,
    'php.web.memory' => '24M',
    'php.display_errors' => 'Off',

    'php.post_max_size' => '1M',
    'php.opcache.validate_timestamps' => 0,

    Config::BRISTOLIAN_DEPLOY_TIME => (new DateTime())->format('Y_m_d_H_i_s'),

    Config::BRISTOLIAN_ASSETS_FORCE_REFRESH => false,
    Config::BRISTOLIAN_COMMIT_SHA => $sha,
    Config::BRISTOLIAN_REDIS_INFO => [
        'host' => 'redis',
        'password' => 'ePvDZpYTXzT5N9xAPu24',
        'port' => 6379
    ],

//    'brsitolian.allowed_access_cidrs' => [
//        '86.7.192.0/24',
//        '10.0.0.0/8',
//        '127.0.0.1/24',
//        "172.0.0.0/8",   // docker local networking
//        '192.168.0.0/16'
//    ]

    Config::BRISTOLIAN_SQL_HOST => getEnvString('MYSQL_HOST'),
    Config::BRISTOLIAN_SQL_DATABASE => getEnvString('MYSQL_DATABASE'),
    Config::BRISTOLIAN_SQL_USERNAME => getEnvString('MYSQL_USER'),
    Config::BRISTOLIAN_SQL_PASSWORD => getEnvString('MYSQL_PASSWORD'),
];

// Settings for local development.
$local = [
    'varnish.pass_all_requests' => true,
    'varnish.allow_non_ssl' => true,
    'system.build_debug_php_containers' => true,

    'php.display_errors' => 'On',
    'php.opcache.validate_timestamps' => 1,

    Config::BRISTOLIAN_ENVIRONMENT => 'local',
    Config::BRISTOLIAN_ASSETS_FORCE_REFRESH => true,
];

$prod = [
    Config::BRISTOLIAN_ENVIRONMENT => 'prod',
];

$varnish_debug = [
    'varnish.pass_all_requests' => false
];


