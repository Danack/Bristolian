<?php

declare(strict_types = 1);

// Holds the functions used in creating objects for the
// API environment.

use Bristolian\Config\Config;
use DI\Injector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Laminas\Diactoros\ResponseFactory;
use SlimDispatcher\DispatchingResolver;
use Bristolian\AppErrorHandler\AppErrorHandler;
use DataType\Exception\ValidationException;

function createJsonAppErrorHandler(
    Config $config,
    DI\Injector $injector
): AppErrorHandler {
    if ($config->isProductionEnv() === true) {
        return $injector->make(\Bristolian\AppErrorHandler\JsonErrorHandlerForProd::class);
    }

    return $injector->make(\Bristolian\AppErrorHandler\JsonErrorHandlerForLocalDev::class);
}



/**
 * Creates the ExceptionMiddleware that converts all known app exceptions
 * to nicely formatted pages for the api
 */
function    createExceptionMiddlewareForApi(\Di\Injector $injector)
{
    $exceptionHandlers = [
//    \TypeSpec\Exception\ValidationException::class =>
//           'paramsValidationExceptionMapperApi',

    \DataType\Exception\ValidationException::class => 'convertValidationExceptionMapperApi',

//        \Bristolian\Exception\DebuggingCaughtException::class => 'debuggingCaughtExceptionExceptionMapperForApi',

        //        \ParseError::class => 'parseErrorMapper',

        \PDOException::class => 'pdoExceptionMapper',

        Slim\Exception\HttpNotFoundException::class => 'convertHttpNotFoundExceptionToResponse',
    ];

    $responseFactory = $injector->make(ResponseFactoryInterface::class);

    return new \Bristolian\Middleware\ExceptionToJsonResponseMiddleware(
        $responseFactory,
        $exceptionHandlers
    );
}

/**
 * @param Injector $injector
 * @param \Bristolian\AppErrorHandler\AppErrorHandler $appErrorHandler
 * @return \Slim\App
 * @throws \Auryn\InjectionException
 */
function createSlimAppForApi(
    Injector $injector,
    \Bristolian\AppErrorHandler\AppErrorHandler $appErrorHandler
): \Slim\App {

    $dispatcher = new \Bristolian\Basic\Dispatcher($injector);

    $callableResolver = new DispatchingResolver(
        $dispatcher,
        $resultMappers = getApiResultMappers($injector)
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
function getApiResultMappers(\DI\Injector $injector)
{
    return [
        \SlimDispatcher\Response\StubResponse::class =>
            'SlimDispatcher\mapStubResponseToPsr7',
        ResponseInterface::class =>
            '\SlimDispatcher\passThroughResponse',
        // Some controllers just want to return a chunk of HTML
        'string' =>
            'Bristolian\StringToHtmlPageConverter::convertStringToHtmlResponse',
    ];
}