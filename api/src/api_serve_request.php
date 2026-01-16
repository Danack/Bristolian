<?php

declare(strict_types = 1);

error_reporting(E_ALL);

require_once __DIR__ . "/../../vendor/autoload.php";

require_once __DIR__ . '/api_convert_exception_to_json_functions.php';
require_once __DIR__ . '/api_factories.php';
require_once __DIR__ . '/api_functions.php';
require_once __DIR__ . '/api_injection_params.php';
require_once __DIR__ . '/api_routes.php';
require_once __DIR__ . '/api_serve_request.php';

require_once __DIR__ . '/../../src/factories.php';
require_once __DIR__ . '/../../src/functions.php';
require_once __DIR__ . '/../../src/functions_tinned_fish.php';
require_once __DIR__ . '/../../src/error_functions.php';

require __DIR__ . "/../../config.generated.php";

set_error_handler('saneErrorHandler');

require __DIR__ . "/../../credentials.php";

$injector = new DI\Injector();
$injectionParams = apiInjectionParams();

$injectionParams->addToInjector($injector);

$logger = $injector->make(Bristolian\Basic\ErrorLogger::class);


// Any class that implements this interface and method,
// can be instantiated through that static method.
$injector->staticFactory(\Bristolian\StaticFactory::class, 'createFromRequest');
$injector->share($injector);

try {
    $app = $injector->make(\Slim\App::class);

    $routes = getAllApiRoutes();
    foreach ($routes as $standardRoute) {
        list($path, $method, $callable) = $standardRoute;
        $slimRoute = $app->map([$method], $path, $callable);
    }

    $app->run();
} catch (\DI\InjectionException $exception) {
    $details = renderInjectionExceptionToJson($exception);
    http_response_code(500);
    echo json_encode(
        $details,
        JSON_PRETTY_PRINT
    );
} catch (\Throwable $exception) {

    showTotalErrorJson($exception);
}
