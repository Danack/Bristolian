<?php

declare(strict_types = 1);

error_reporting(E_ALL);

require_once __DIR__ . "/../../vendor/autoload.php";

require_once __DIR__ . '/../../src/factories.php';
require_once __DIR__ . '/../../src/functions.php';
require_once __DIR__ . '/../../src/error_functions.php';
require_once __DIR__ . '/../../src/site_html.php';
require __DIR__ . "/../../config.generated.php";

require __DIR__ . "/../../credentials.php";



set_error_handler('saneErrorHandler');

$start_time = microtime(true);

function time_it()
{
    global $start_time;

    $end_time = microtime(true);

    $time_taken = ($end_time - $start_time);

    if ($time_taken < 0.00001) {
        echo "Basically nothing.";
        exit(0);
    }

    echo "Time taken = " . $time_taken  . " m'kay.";
    exit(0);
}

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
