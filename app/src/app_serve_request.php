<?php

declare(strict_types = 1);

error_reporting(E_ALL);

require_once __DIR__ . "/../../vendor/autoload.php";

require_once __DIR__ . '/../../src/factories.php';
require_once __DIR__ . '/../../src/functions.php';
require_once __DIR__ . '/../../src/error_functions.php';
require_once __DIR__ . '/../../src/site_html.php';
require __DIR__ . "/../../config.generated.php";

set_error_handler('saneErrorHandler');


$injector = new DI\Injector();
$injectionParams = injectionParams();
$injectionParams->addToInjector($injector);
$injector->share($injector);

try {
    $app = $injector->make(\Slim\App::class);

    $routes = getAllAppRoutes();
    foreach ($routes as $standardRoute) {
        list($path, $method, $callable) = $standardRoute;
        $slimRoute = $app->map([$method], $path, $callable);
    }

    $app->run();
}
catch (\DI\InjectionException $exception) {
    [$text, $status] = renderInjectionExceptionToHtml($exception);

    echo $text;
}
catch (\Throwable $exception) {
    showTotalErrorPage($exception);
}
