<?php

declare(strict_types=1);

if (array_key_exists('REQUEST_URI', $_SERVER) === true) {
    $uri = $_SERVER['REQUEST_URI'];
    if (strpos($uri, "/api") === 0) {
        // Technically, it would be better to have separate server pools
        require_once __DIR__ ."/../../api/public/index.php";
        exit(0);
    }
}

require_once __DIR__ . "/../src/app_serve_request.php";
