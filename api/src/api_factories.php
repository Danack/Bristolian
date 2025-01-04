<?php

declare(strict_types = 1);

// Holds the functions used in creating objects for the
// API environment.

use Bristolian\Config\Config;
use DI\Injector;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use SlimDispatcher\DispatchingResolver;
use Bristolian\Middleware\ExceptionToJsonResponseMiddleware;

/**
 * Creates the ExceptionMiddleware that converts all known app exceptions
 * to nicely formatted pages for the api
 */
function createExceptionMiddlewareForApi(\Di\Injector $injector): ExceptionToJsonResponseMiddleware
{
    $exceptionHandlers = [
        \DataType\Exception\ValidationException::class => 'convertValidationExceptionMapperApi',
        \Bristolian\Exception\InvalidPermissionsException::class => 'convertInvalidPermissionsExceptionToResponse',
        \PDOException::class => 'pdoExceptionMapper',
        Slim\Exception\HttpNotFoundException::class => 'convertHttpNotFoundExceptionToResponse',
        \Bristolian\Exception\UnauthorisedException::class => 'convertUnauthorisedExceptionToResponse',

        \Throwable::class =>'convertGenericThrowableToResponse', // must be last
    ];

    $responseFactory = $injector->make(ResponseFactoryInterface::class);

    return new \Bristolian\Middleware\ExceptionToJsonResponseMiddleware(
        $responseFactory,
        $exceptionHandlers
    );
}

/**
 * @param Injector $injector
 * @return \Slim\App
 * @throws \Auryn\InjectionException
 */
function createSlimAppForApi(
    Injector $injector,
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

    // Slim processes middleware in a Last In, First Out (LIFO) order.
    // This means the last middleware added is the first one to be executed.
    // If you add multiple middleware components, they will be executed
    // in the reverse order of their addition.
    $app->add($injector->make(\Bristolian\Middleware\PermissionsCheckHtmlMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\AllowAllCors::class));
    $app->add($injector->make(\Bristolian\Middleware\ExceptionToJsonResponseMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\MemoryCheckMiddleware::class));
    $app->add($injector->make(\Bristolian\Middleware\AppSessionMiddleware::class));

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
        // Some controllers just want to return a chunk of JSON...do they?
        // seems bogus in an API environment.
        'string' => 'convertStringToResponse',
    ];
}