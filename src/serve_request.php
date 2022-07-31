<?php

declare(strict_types = 1);

error_reporting(E_ALL);

require_once __DIR__ . "/../vendor/autoload.php";

require_once __DIR__ . '/factories.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/error_functions.php';
require_once __DIR__ . '/site_html.php';
require_once __DIR__ . '/slim_functions.php';
require __DIR__ . "/../config.generated.php";

set_error_handler('saneErrorHandler');

$injector = new Auryn\Injector();
$injectionParams = injectionParams();
$injectionParams->addToInjector($injector);
$injector->share($injector);

try {
    $app = $injector->make(\Slim\App::class);

    $routes = getAllRoutes();
    foreach ($routes as $standardRoute) {
        list($path, $method, $callable) = $standardRoute;
        $slimRoute = $app->map([$method], $path, $callable);
    }

    $app->run();
}
catch (\Throwable $exception) {
    showTotalErrorPage($exception);
}

