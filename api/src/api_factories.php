<?php

declare(strict_types = 1);

use Auryn\Injector;
use Bristolian\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use SlimAuryn\AurynCallableResolver;
use Laminas\Diactoros\ResponseFactory;


function createJsonAppErrorHandler(
    Config $config,
    \Auryn\Injector $injector
) : \Bristolian\AppErrorHandler\AppErrorHandler {
    if ($config->isProductionEnv() === true) {
        return $injector->make(\Bristolian\AppErrorHandler\JsonErrorHandlerForProd::class);
    }

    return $injector->make(\Bristolian\AppErrorHandler\JsonErrorHandlerForLocalDev::class);
}

//function createRoutesForApi()
//{
//    return new \SlimAuryn\Routes(__DIR__ . '/../api/src/api_routes.php');
//}



/**
 * Creates the ExceptionMiddleware that converts all known app exceptions
 * to nicely formatted pages for the api
 */
function createExceptionMiddlewareForApi(\Auryn\Injector $injector)
{
    $exceptionHandlers = [
        \TypeSpec\Exception\ValidationException::class => 'paramsValidationExceptionMapperApi',
//        \Bristolian\Exception\DebuggingCaughtException::class => 'debuggingCaughtExceptionExceptionMapperForApi',
        //        \ParseError::class => 'parseErrorMapper',
//        \PDOException::class => 'pdoExceptionMapper',
    ];

    $responseFactory = $injector->make(ResponseFactoryInterface::class);

    return new \Bristolian\Middleware\ExceptionToJsonResponseMiddleware(
        $responseFactory,
        $exceptionHandlers
    );
}

function createSlimAppForApi(
    Injector $injector,
    \Bristolian\AppErrorHandler\AppErrorHandler $appErrorHandler
) {

    $callableResolver = new AurynCallableResolver(
        $injector,
        $resultMappers = getResultMappers($injector)
    );

    $app = new \Slim\App(
        /* ResponseFactoryInterface */ $responseFactory = new ResponseFactory(),
        /* ?ContainerInterface */ $container = null,
        /* ?CallableResolverInterface */ $callableResolver,
        /* ?RouteCollectorInterface */ $routeCollector = null,
        /* ?RouteResolverInterface */ $routeResolver = null,
        /* ?MiddlewareDispatcherInterface */ $middlewareDispatcher = null
    );

    $app->add($injector->make(\Bristolian\Middleware\ExceptionToJsonResponseMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\MemoryCheckMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\AllowAllCors::class));

    return $app;
}


/**
 * Creates the objects that map StubResponse into PSR7 responses
 * @return mixed
 */
function getResultMappers(\Auryn\Injector $injector)
{
    return [
        \SlimAuryn\Response\JsonResponse::class =>
            'SlimAuryn\mapStubResponseToPsr7',
        SlimAuryn\Response\TextResponse::class =>
            'SlimAuryn\mapStubResponseToPsr7',
        ResponseInterface::class =>
          'SlimAuryn\passThroughResponse'
    ];
}